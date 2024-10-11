<?php

namespace App\Console\Commands\StatisticsReport;

use App\Provider;
use App\Patient;
use App\Appointment;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateOneVisitWithProviderReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics-report:one-visit-with-provider';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $providers = Provider::withTrashed()
            ->where('is_test', 0)
            ->get();

        $data = [];
        foreach ($providers as $provider) {
            Patient::select([
                'patients.id',
                \DB::raw("CONCAT(`patients`.`first_name`, ' ', `patients`.`last_name`) AS patient_name"),
                'patient_statuses.status',
                'patient_insurances.insurance',
                \DB::raw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) as patient_created_at"),
            ])
                ->selectRaw("(SELECT COUNT(`appointments`.`id`) FROM appointments WHERE `appointments`.`patients_id` = `patients`.`id` AND `appointments`.`providers_id` = {$provider->id} AND `appointments`.`appointment_statuses_id` = 1 AND `appointments`.`deleted_at` IS NULL) AS visits_count")
                ->selectRaw("(SELECT DATE(FROM_UNIXTIME(MIN(`appointments`.`time`))) FROM appointments WHERE `appointments`.`patients_id` = `patients`.`id` AND `appointments`.`providers_id` = {$provider->id} AND `appointments`.`appointment_statuses_id` = 1 AND `appointments`.`deleted_at` IS NULL) AS date_of_service")
                ->havingRaw('visits_count = 1')
                ->leftJoin('patient_statuses', 'patient_statuses.id', '=', 'patients.status_id')
                ->leftJoin('patient_insurances', 'patient_insurances.id', '=', 'patients.primary_insurance_id')
                ->where('patients.is_test', 0)
                ->get()
                ->each(function ($patient) use (&$data, &$provider) {
                    $data[] = [
                        'Patient ID' => $patient->id,
                        'Patient Name' => $patient->patient_name,
                        'Patient Status' => $patient->status,
                        'Insurance' => $patient->insurance,
                        'Patient Created At' => $patient->patient_created_at,
                        'Provider Name' => $provider->provider_name,
                        'Provider Status' => isset($provider->deleted_at) ? 'not active' : 'active',
                        'Date of Service' => $patient->date_of_service,
                        'Patient Total Visits Count' => $this->getVisitsCount($patient->id),
                        'EHR Link' => "https://admin.cwr.care/chart/{$patient->id}",
                    ];
                });
        }

        $this->generateExcelReport($data);
    }

    private function getVisitsCount($patientId)
    {
        return Appointment::query()
            ->selectRaw('COUNT(`appointments`.`id`) AS visits_count')
            ->where('appointments.patients_id', '=', $patientId)
            ->where('appointments.appointment_statuses_id', '=', 1)
            ->groupBy('appointments.patients_id')
            ->first()['visits_count'];
    }

    private function generateExcelReport($visitsData)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $titles = [
            'Patient ID',
            'Patient Name',
            'Patient Status',
            'Insurance',
            'Patient Created At',
            'Provider Name',
            'Provider Status',
            'Date of Service',
            'Patient Total Visits Count',
            'EHR Link',
        ];

        $worksheet->fromArray([$titles], NULL, 'A1');
        $worksheet->fromArray($visitsData, NULL, 'A2');

        $writer = new Xlsx($spreadsheet);
        $filename = "patients_with_one_visit_statistics.xlsx";
        $path = storage_path('app/temp/' . $filename);
        $writer->save($path);
    }
}

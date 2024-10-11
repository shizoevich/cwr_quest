<?php

namespace App\Console\Commands\StatisticsReport;

use App\Availability;
use App\AvailabilityType;
use App\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateLucetAppointmentsScheduledOnProvidersAvailabilityReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics-report:lucet-appointments-scheduled-on-providers-availability';

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
        $data = $this->getData();

        $this->generateExcelReport($data);
    }

    private function getData(): array
    {
        $data = [];
        $availabilitiesData = [];

        Availability::query()
            ->select([
                'availabilities.id',
                'availabilities.provider_id',
                'providers.provider_name',
                'availabilities.start_date',
                'availabilities.start_time',
                'availabilities.length',
                'availabilities.created_at',
            ])
            ->where('availabilities.availability_type_id', AvailabilityType::getForNewPatientsId())
            ->join('providers', 'availabilities.provider_id', '=', 'providers.id')
            ->orderBy('availabilities.start_date', 'desc')
            ->orderBy('providers.provider_name', 'asc')
            ->orderBy('availabilities.start_time', 'asc')
            ->each(function ($availability) use (&$availabilitiesData) {
                $duration = 0;
                $startDateTime = Carbon::parse($availability->start_date)->setTimeFromTimeString($availability->start_time);

                while ($availability->length > $duration) {
                    $startTimestamp = $startDateTime->copy()->addMinutes($duration)->timestamp;

                    $availabilitiesData[] = [
                        'id' => $availability->id,
                        'provider_id' => $availability->provider_id,
                        'provider_name' => $availability->provider_name,
                        'date' => Carbon::parse($availability->start_date)->toDateString(),
                        'start_timestamp' => $startTimestamp,
                        'end_timestamp' => $startTimestamp + 3600, // add one hour in seconds (60*60)
                        'created_at' => $availability->created_at,
                    ];

                    $duration += 60;

                    $this->info($availability->id . ' ' . $duration);
                }
            });

        dump('--------------------------------------');
        dump('------ Got all availabilities --------');
        dump('--------------------------------------');

        $firstAppointmentIdSql = 'SELECT appt.id FROM appointments appt WHERE appt.patients_id=appointments.patients_id ORDER BY appt.time LIMIT 1';

        foreach ($availabilitiesData as $availabilityData) {
            $appointmentQuery = Appointment::query()
                ->select([
                    'appointments.id',
                    DB::raw('CONCAT(patients.first_name, " ", patients.last_name) AS patient_name'),
                    'kaiser_appointments.created_at AS lucet_appointment_created_at',
                    DB::raw("($firstAppointmentIdSql) as first_appointment_id"),
                ])
                ->join('kaiser_appointments', function ($join) {
                    // Field kaiser_appointments.internal_id was added only in May 2021,
                    // but there are some records since 2019, so it is not used in join clause below
                    $join->on('kaiser_appointments.patient_id', '=', 'appointments.patients_id')
                        ->on('kaiser_appointments.provider_id', '=', 'appointments.providers_id')
                        ->on('kaiser_appointments.start_date', '=', DB::raw('FROM_UNIXTIME(appointments.time)'));
                })
                ->join('patients', 'appointments.patients_id', '=', 'patients.id')
                ->where('appointments.providers_id', $availabilityData['provider_id'])
                ->where('appointments.time', '>=', $availabilityData['start_timestamp'])
                ->where('appointments.time', '<', $availabilityData['end_timestamp'])
                ->havingRaw('appointments.id = first_appointment_id');

            if ($appointmentQuery->exists()) {
                $appointmentData = $appointmentQuery->first();

                $startTime = Carbon::createFromTimestamp($availabilityData['start_timestamp'])->format('H:i');
                $endTime = Carbon::createFromTimestamp($availabilityData['end_timestamp'])->format('H:i');

                $this->info('Add data for: ' . $availabilityData['id']);

                $data[] = [
                    'Provider Name' => $availabilityData['provider_name'],
                    'Patient Name' => $appointmentData['patient_name'],
                    'Availability Created At' => $availabilityData['created_at'],
                    'Availability Date' => $availabilityData['date'],
                    'Availability Time' => $startTime . ' - ' . $endTime,
                    'Lucet Appointment Created At' => $appointmentData['lucet_appointment_created_at'],
                ];
            }
        }

        dump('--------------------------------------');
        dump('------ Finished collecting data ------');
        dump('--------------------------------------');

        return $data;
    }

    private function generateExcelReport($visitsData): void
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $titles = [
            'Provider Name',
            'Patient Name',
            'Availability Created At',
            'Availability Date',
            'Availability Time',
            'Lucet Appointment Created At',
        ];

        $worksheet->fromArray([$titles], NULL, 'A1');
        $worksheet->fromArray($visitsData, NULL, 'A2');

        $writer = new Xlsx($spreadsheet);
        $filename = "lucet_appointments_scheduled_on_providers_availability.xlsx";
        $path = storage_path('app/temp/' . $filename);
        $writer->save($path);
    }
}

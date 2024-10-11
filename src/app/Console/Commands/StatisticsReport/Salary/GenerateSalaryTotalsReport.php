<?php

namespace App\Console\Commands\StatisticsReport\Salary;

use App\Models\Billing\BillingPeriod;
use App\Models\Billing\BillingPeriodType;
use App\Patient;
use App\Models\Patient\Lead\PatientLead;
use App\Models\Patient\Inquiry\PatientInquiry;
use App\Appointment;
use App\Status;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateSalaryTotalsReport extends Command
{
    private const EMPTY_ROW = [['label' => '', 'value' => '']];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary-report:totals';

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
        $previousPeriod = BillingPeriod::getPrevious(BillingPeriodType::TYPE_BI_WEEKLY);
        $data = $this->prepareData($previousPeriod->start_date, $previousPeriod->end_date);
        $this->generateExcelReport($data, $previousPeriod->start_date, $previousPeriod->end_date);
    }

    private function prepareData(string $startDate, string $endDate): array
    {
        return [
            $this->prepareAppointmentsData($startDate, $endDate),
            $this->preparePatientsData($startDate, $endDate),
            $this->prepareNewPatientsData($startDate, $endDate)
        ];
    }

    private function generateExcelReport(array $data, string $startDate, string $endDate): void
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach ($data as $item) {
            $activeWorksheet = $spreadsheet->createSheet();
            $activeWorksheet->setTitle($item['title']);
            $activeWorksheet->fromArray($item['data'], null, 'A1');
        }

        $writer = new Xlsx($spreadsheet);
        $filename = "totals_{$startDate}__{$endDate}.xlsx";
        $path = storage_path('app/temp/' . $filename);
        $writer->save($path);
    }

    private function prepareAppointmentsData(string $startDate, string $endDate)
    {
        $this->info('Prepare data: appointments');

        $data = array_merge(
            $this->getAppointmentsCountByStatus($startDate, $endDate),
            self::EMPTY_ROW,
            self::EMPTY_ROW,
            $this->getVisitsCountByInsurance($startDate, $endDate),
            self::EMPTY_ROW,
            self::EMPTY_ROW,
            $this->getVisitsCountByModality($startDate, $endDate)
        );

        return [
            'title' => 'Appointments Statistics',
            'data' => $data
        ];
    }

    private function getAppointmentsCountByStatus(string $startDate, string $endDate)
    {
        $columnNames = [
            'label' => 'Appt. Status',
            'value' => 'Count'
        ];

        $data = Appointment::query()
            ->selectRaw('`appointment_statuses`.`status`, COUNT(`appointments`.`id`) as appointments_count')
            ->join('appointment_statuses', 'appointment_statuses.id', '=', 'appointments.appointment_statuses_id') 
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->join('providers', 'providers.id', '=', 'appointments.providers_id')
            ->where('patients.is_test', 0)
            ->where('providers.is_test', 0)
            ->whereRaw("DATE(FROM_UNIXTIME(`appointments`.`time`)) >= DATE('{$startDate}')")
            ->whereRaw("DATE(FROM_UNIXTIME(`appointments`.`time`)) <= DATE('{$endDate}')")
            ->groupBy('appointments.appointment_statuses_id')
            ->orderBy('appointment_statuses.status')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->status,
                    'value' => $item->appointments_count
                ];
            })
            ->all();

        return array_merge([$columnNames], $data);
    }

    private function getVisitsCountByInsurance(string $startDate, string $endDate)
    {
        $columnNames = [
            'label' => 'Insurance',
            'value' => 'Visits Count'
        ];

        $data = Appointment::query()
            ->selectRaw('`patients`.`primary_insurance`, COUNT(`appointments`.`id`) as appointments_count')
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->join('providers', 'providers.id', '=', 'appointments.providers_id')
            ->where('patients.is_test', 0)
            ->where('providers.is_test', 0)
            ->whereIn('appointment_statuses_id', Status::getCompletedVisitCreatedStatusesId())
            ->whereRaw("DATE(FROM_UNIXTIME(`appointments`.`time`)) >= DATE('{$startDate}')")
            ->whereRaw("DATE(FROM_UNIXTIME(`appointments`.`time`)) <= DATE('{$endDate}')")
            ->groupBy('patients.primary_insurance_id')
            ->orderBy('patients.primary_insurance')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->primary_insurance,
                    'value' => $item->appointments_count
                ];
            })
            ->all();

        return array_merge([$columnNames], $data);
    }

    private function getVisitsCountByModality(string $startDate, string $endDate)
    {
        $columnNames = [
            'label' => 'Treatment Modality',
            'value' => 'Visits Count'
        ];

        $data = Appointment::query()
            ->selectRaw('`treatment_modalities`.`name`, COUNT(`appointments`.`id`) as appointments_count')
            ->join('treatment_modalities', 'treatment_modalities.id', '=', 'appointments.treatment_modality_id')
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->join('providers', 'providers.id', '=', 'appointments.providers_id')
            ->where('patients.is_test', 0)
            ->where('providers.is_test', 0)
            ->whereIn('appointment_statuses_id', Status::getCompletedVisitCreatedStatusesId())
            ->whereRaw("DATE(FROM_UNIXTIME(`appointments`.`time`)) >= DATE('{$startDate}')")
            ->whereRaw("DATE(FROM_UNIXTIME(`appointments`.`time`)) <= DATE('{$endDate}')")
            ->groupBy('appointments.treatment_modality_id')
            ->orderBy('treatment_modalities.name')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->name,
                    'value' => $item->appointments_count
                ];
            })
            ->all();
        
        return array_merge([$columnNames], $data);
    }

    private function preparePatientsData(string $startDate, string $endDate)
    {
        $this->info('Prepare data: patients');

        $data = array_merge(
            $this->getPatientsCountByStatus($startDate, $endDate),
            self::EMPTY_ROW,
            self::EMPTY_ROW,
            $this->getPatientsCountByVisitsFrequency($startDate, $endDate)
        );

        return [
            'title' => 'Patients Statistics',
            'data' => $data
        ];
    }

    private function getPatientsCountByStatus(string $startDate, string $endDate)
    {
        $columnNames = [
            'label' => 'Patient Status',
            'value' => 'Count'
        ];

        // get patients count whose status was changed before the end of the period
        $statusMapping = Patient::query()
            ->selectRaw('`patient_statuses`.`status`, COUNT(`patients`.`id`) as patients_count')
            ->join('patient_statuses', 'patient_statuses.id', '=', 'patients.status_id')
            ->whereDate('patients.status_updated_at', '<=', $endDate)
            ->where('patients.is_test', 0)
            ->groupBy('patients.status_id')
            ->get()
            ->reduce(function ($carry, $item) {
                $carry[$item->status] = $item->patients_count;
                
                return $carry;
            }, []);

        // get patients count whose status was changed after the end of the period
        $patientIds = Patient::query()
            ->select('patients.id')
            ->whereDate('patients.status_updated_at', '>', $endDate)
            ->where('patients.is_test', 0)
            ->get()
            ->pluck('id');

        foreach ($patientIds as $id) {
            $log = \DB::connection('mysql_logger')
                ->table('hipaa_log_item')
                ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.status_name')) as status_name")
                ->whereDate('appeared_at', '<=', $endDate)
                ->where('event_name_id', Patient::getEventNamePatientUpdate())
                ->where('dirty_fields', 'like', '%status_id%')
                ->where('data', 'like', '%"id":' . $id . '%')
                ->orderBy('appeared_at', 'desc')
                ->first();

            if (isset($log) && isset($log->status_name)) {
                $statusMapping[$log->status_name] = isset($statusMapping[$log->status_name]) ? ($statusMapping[$log->status_name] + 1) : 1;
            }
        }

        $data = [];
        foreach ($statusMapping as $key => $value) {
            $data[] = [
                'label' => $key,
                'value' => $value
            ];
        }

        return array_merge([$columnNames], $data);
    }

    private function getPatientsCountByVisitsFrequency(string $startDate, string $endDate)
    {
        $columnNames = [
            'label' => 'Frequency of Treatment',
            'value' => 'Count'
        ];

        // get patients whose "visits-frequency" was changed before the end of the period
        $visitFrequencyMapping = Patient::query()
            ->select([
                'patients.id',
                'patient_visit_frequencies.name AS visit_frequency',
                \DB::raw("(
                    SELECT `patients_visit_frequency_changes`.`created_at`
                    FROM `patients_visit_frequency_changes`
                    WHERE `patients_visit_frequency_changes`.`patient_id` = `patients`.`id`
                    ORDER BY `patients_visit_frequency_changes`.`created_at` DESC
                    LIMIT 1
                ) AS visit_frequency_updated_at"),
                \DB::raw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) as patient_created_at"),
            ])
            ->join('patient_visit_frequencies', 'patient_visit_frequencies.id', '=', 'patients.visit_frequency_id')
            ->whereNotNull('patients.visit_frequency_id')
            ->where('patients.is_test', 0)
            ->havingRaw("(
                (visit_frequency_updated_at IS NULL AND DATE(patient_created_at) <= DATE('{$endDate}'))
                    OR DATE(visit_frequency_updated_at) <= DATE('{$endDate}')
            )")
            ->get()
            ->reduce(function ($carry, $item) {
                $carry[$item->visit_frequency] = isset($carry[$item->visit_frequency]) ? ($carry[$item->visit_frequency] + 1) : 1;
                
                return $carry;
            }, []);
        
        // get patients without "visits-frequency"
        $visitFrequencyMapping['Not set'] = Patient::query()
            ->whereNull('visit_frequency_id')
            ->whereRaw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) <= DATE('{$endDate}')")
            ->where('patients.is_test', 0)
            ->count();

        // get patients whose "visits-frequency" was changed after the end of the period
        $joinQuery = "SELECT MIN(`id`), `patient_id`, `old_visit_frequency_id`
            FROM `patients_visit_frequency_changes`
            WHERE DATE(`created_at`) > DATE('{$endDate}')
            GROUP BY patient_id";
        
        Patient::query()
            ->select([
                'patients.id',
                'patient_visit_frequencies.name AS visit_frequency',
                \DB::raw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) as patient_created_at"),
            ])
            ->join(
                \DB::raw("($joinQuery) as pvfc"),
                'patients.id',
                '=',
                'pvfc.patient_id'
            )
            ->leftJoin('patient_visit_frequencies', 'patient_visit_frequencies.id', '=', 'pvfc.old_visit_frequency_id')
            ->whereNotNull('patients.visit_frequency_id')
            ->where('patients.is_test', 0)
            ->havingRaw("DATE(patient_created_at) <= DATE('{$endDate}')")
            ->get()
            ->each(function ($item) use (&$visitFrequencyMapping) {
                $label = $item->visit_frequency ?? 'Not set';
                $visitFrequencyMapping[$label] = isset($visitFrequencyMapping[$label]) ? ($visitFrequencyMapping[$label] + 1) : 1;
            });

        $data = [];
        foreach ($visitFrequencyMapping as $key => $value) {
            $data[] = [
                'label' => $key,
                'value' => $value
            ];
        }

        return array_merge([$columnNames], $data);
    }

    private function prepareNewPatientsData(string $startDate, string $endDate)
    {
        $this->info('Prepare data: new patients');

        $patientQuery = Patient::select(['patients.id'])
            ->whereRaw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) >= DATE('{$startDate}')")
            ->whereRaw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) <= DATE('{$endDate}')")
            ->where('patients.is_test', 0);

        $patientLeadQuery = PatientLead::select(['patient_leads.id'])
            ->whereDate('patient_leads.created_at', '>=', $startDate)
            ->whereDate('patient_leads.created_at', '<=', $endDate)
            ->whereNull('patient_leads.patient_id');

        $returningPatientQuery = PatientInquiry::select(['patient_inquiries.id'])
            ->wherePatientIsCreated()
            ->where('patient_inquiries.is_returning', 1)
            ->join('patients', 'patients.id', '=', 'patient_inquiries.inquirable_id')
            ->whereDate('patient_inquiries.created_at', '>=', $startDate)
            ->whereDate('patient_inquiries.created_at', '<=', $endDate)
            ->where('patients.is_test', 0);
        
        $noChartPtsCount = (clone $patientLeadQuery)->count();
        $returningPtsCount = (clone $returningPatientQuery)->count();
        $noApptsPtsCount = (clone $patientQuery)
            ->leftJoin('appointments', function($join) {
                $join->on($join->table . '.patients_id', '=', 'patients.id')
                    ->whereNull($join->table . '.deleted_at');
            })
            ->whereNull('appointments.id')
            ->count();
        $noVisitsPtsCount = (clone $patientQuery)
            ->selectRaw('(SELECT COUNT(`appointments`.`id`) FROM appointments WHERE `appointments`.`patients_id` = `patients`.`id` AND `appointments`.`appointment_statuses_id` = 1 AND `appointments`.`deleted_at` IS NULL) AS visits_count')
            ->selectRaw('(SELECT COUNT(`appointments`.`id`) FROM appointments WHERE `appointments`.`patients_id` = `patients`.`id` AND `appointments`.`appointment_statuses_id` != 1 AND `appointments`.`deleted_at` IS NULL) AS appt_count')
            ->havingRaw('visits_count = 0')
            ->havingRaw('appt_count > 0')
            ->get()
            ->count();
        $oneVisitPtsCount = (clone $patientQuery)
            ->selectRaw('(SELECT COUNT(`appointments`.`id`) FROM appointments WHERE `appointments`.`patients_id` = `patients`.`id` AND `appointments`.`appointment_statuses_id` = 1 AND `appointments`.`deleted_at` IS NULL) AS visits_count')
            ->havingRaw('visits_count = 1')
            ->get()
            ->count();
        $fewVisitsPtsCount = (clone $patientQuery)
            ->selectRaw('(SELECT COUNT(`appointments`.`id`) FROM appointments WHERE `appointments`.`patients_id` = `patients`.`id` AND `appointments`.`appointment_statuses_id` = 1 AND `appointments`.`deleted_at` IS NULL) AS visits_count')
            ->havingRaw('visits_count > 1')
            ->get()
            ->count();

        $data = [
            [
                'label' => '# of new patients (inquiry only no chart created)',
                'value' => $noChartPtsCount
            ],
            [
                'label' => '# of returning patients',
                'value' => $returningPtsCount
            ],
            [
                'label' => '# of new patients (chart created but no appt)',
                'value' => $noApptsPtsCount
            ],
            [
                'label' => '# of new patients (chart created + 1 or more appts but no visits)',
                'value' => $noVisitsPtsCount
            ],
            [
                'label' => '# of new patients (chart created + 1 visit only)',
                'value' => $oneVisitPtsCount
            ],
            [
                'label' => '# of new patients (chart created + 2 or more visits)',
                'value' => $fewVisitsPtsCount
            ]
        ];

        return [
            'title' => 'New Patients Statistics',
            'data' => $data
        ];
    }
}

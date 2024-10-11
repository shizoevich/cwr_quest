<?php

namespace App\Console\Commands\StatisticsReport;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Patient;
use App\Models\Patient\Lead\PatientLead;
use App\Models\Patient\Inquiry\PatientInquiry;
use App\Appointment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class GenerateNewPatientsReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics-report:new-patients';

    private const SUMMARY_TITLES = [
        'Month',
        'Total # of patients',
        '# of new patients (inquiry only no chart created)',
        '# of returning patients',
        '# of new patients (chart created but no appt)',
        '# of new patients (chart created + 1 or more appts but no visits)',
        '# of new patients (chart created + 1 visit only)',
        '# of new patients (chart created + 2 or more visits)',
        '# of patients still active today'
    ];

    private const DEFAULT_PTS_TITLES = [
        'Patient ID',
        'Patient Name',
        'Patient Status',
        'Insurance',
        'Patient Created At',
        'Providers',
        'First Visit Date',
        'Last Visit Date',
        'Visits Count',
        'EHR Link',
    ];

    private const TOTAL_PTS_TITLES = [
        'Patient ID',
        'Patient Name',
        'Patient Status',
        'Insurance',
        'Patient Created At',
        'Patient Returned At',
        'Providers',
        'First Visit Date',
        'Last Visit Date',
        'Visits Count',
        'EHR Link',
    ];

    private const NO_CHART_PTS_TITLES = [
        'Patient ID',
        'Patient Name',
        'Patient Status',
        'Providers',
        'Insurance',
        'Patient Created At',
    ];

    private const RETURNING_PTS_TITLES = [
        'Patient ID',
        'Patient Name',
        'Patient Status',
        'Insurance',
        'Patient Created At',
        'Patient Returned At',
        'Providers',
        'First Visit Date',
        'Last Visit Date',
        'Visits Count',
        'EHR Link',
    ];

    private const NO_APPTS_PTS_TITLES = [
        'Patient ID',
        'Patient Name',
        'Patient Status',
        'Providers',
        'Insurance',
        'Patient Created At',
        'EHR Link',
    ];

    private const NO_VISITS_PTS_TITLES = [
        'Patient ID',
        'Patient Name',
        'Patient Status',
        'Insurance',
        'Patient Created At',
        'Providers',
        'EHR Link',
    ];

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
        $startDate = Carbon::today()->startOfYear();
        $endDate = Carbon::today()->endOfMonth();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $data = [
            $this->prepareSummaryStatistics($startDate, $endDate),
            $this->prepareTotalPtsStatistics($startDate, $endDate),
            $this->prepareNoChartPtsStatistics($startDate, $endDate),
            $this->prepareReturningPtsStatistics($startDate, $endDate),
            $this->prepareNoApptsPtsStatistics($startDate, $endDate),
            $this->prepareNoVisitsPtsStatistics($startDate, $endDate),
            $this->prepareOneVisitPtsStatistics($startDate, $endDate),
            $this->prepareFewVisitsPtsStatistics($startDate, $endDate),
            $this->prepareActivePtsCountStatistics($startDate, $endDate),
        ];

        foreach ($data as $item) {
            $activeWorksheet = $spreadsheet->createSheet();
            $activeWorksheet->setTitle($item['title']);
            $activeWorksheet->fromArray([$item['columnNames']], null, 'A1');
            $activeWorksheet->fromArray($item['data'], null, 'A2');
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'new_patients_statistics_2024.xlsx';
        $path = storage_path('app/temp/' . $filename);
        $writer->save($path);
    }

    public function prepareSummaryStatistics($startDate, $endDate)
    {
        $data = [];
        foreach (CarbonPeriod::create($startDate, '1 month', $endDate) as $date) {
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            $patientQuery = Patient::select(['patients.id'])
                ->whereRaw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) >= DATE('{$start->toDateString()}')")
                ->whereRaw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) <= DATE('{$end->toDateString()}')")
                ->where('patients.is_test', 0);

            $patientLeadQuery = PatientLead::select(['patient_leads.id'])
                ->whereDate('patient_leads.created_at', '>=', $start->toDateString())
                ->whereDate('patient_leads.created_at', '<=', $end->toDateString())
                ->whereNull('patient_leads.patient_id');

            $returningPatientQuery = PatientInquiry::select(['patient_inquiries.id'])
                ->wherePatientIsCreated()
                ->where('patient_inquiries.is_returning', 1)
                ->join('patients', 'patients.id', '=', 'patient_inquiries.inquirable_id')
                ->whereDate('patient_inquiries.created_at', '>=', $start->toDateString())
                ->whereDate('patient_inquiries.created_at', '<=', $end->toDateString())
                ->where('patients.is_test', 0);
            
            $totalPtsCount = (clone $patientQuery)->count()
                + (clone $patientLeadQuery)->count() 
                + (clone $returningPatientQuery)->count();
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
            $activePtsCount = (clone $patientQuery)
                ->where('patients.status_id', 1)
                ->count();

            $data[] = [
                'Month' => $start->format('m/d/Y') . ' - ' . $end->format('m/d/Y'),
                'Total # of patients' => $totalPtsCount,
                '# of new patients (inquiry only no chart created)' => $noChartPtsCount,
                '# of returning patients' => $returningPtsCount,
                '# of new patients (chart created but no appt)' => $noApptsPtsCount,
                '# of new patients (chart created + 1 or more appts but no visits)' => $noVisitsPtsCount,
                '# of new patients (chart created + 1 visit only)' => $oneVisitPtsCount,
                '# of new patients (chart created + 2 or more visits)' => $fewVisitsPtsCount,
                '# of patients still active today' => $activePtsCount
            ];
        }
        
        return [
            'title' => 'Summary',
            'columnNames' => self::SUMMARY_TITLES,
            'data' => $data
        ];
    }

    public function prepareTotalPtsStatistics($start, $end)
    {
        $data = [];

        $patientQuery = Patient::select([
            'patients.id',
            \DB::raw("CONCAT(`patients`.`first_name`, ' ', `patients`.`last_name`) AS patient_name"),
            'patient_statuses.status',
            'patient_insurances.insurance',
            \DB::raw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) as patient_created_at"),
        ])
            ->leftJoin('patient_statuses', 'patient_statuses.id', '=', 'patients.status_id')
            ->leftJoin('patient_insurances', 'patient_insurances.id', '=', 'patients.primary_insurance_id')
            ->whereRaw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) >= DATE('{$start->toDateString()}')")
            ->whereRaw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) <= DATE('{$end->toDateString()}')")
            ->where('patients.is_test', 0)
            ->get()
            ->each(function ($patient) use (&$data) {
                $data[] = [
                    'Patient ID' => $patient->id,
                    'Patient Name' => $patient->patient_name,
                    'Patient Status' => $patient->status,
                    'Insurance' => $patient->insurance,
                    'Patient Created At' => $patient->patient_created_at,
                    'Patient Returned At' => '',
                    'Providers' => $this->getPatientProviders($patient->id),
                    'First Visit Date' => $this->getFirstVisitDate($patient->id),
                    'Last Visit Date' => $this->getLastVisitDate($patient->id),
                    'Visits Count' => $this->getVisitsCount($patient->id),
                    'EHR Link' => "https://admin.cwr.care/chart/{$patient->id}",
                ];
            });

        $patientLeadQuery = PatientLead::select([
            'patient_leads.id',
            \DB::raw("CONCAT(`patient_leads`.`first_name`, ' ', `patient_leads`.`last_name`) AS patient_name"),
            'patient_insurances.insurance',
            'patient_leads.created_at AS patient_created_at'
        ])
            ->leftJoin('patient_insurances', 'patient_insurances.id', '=', 'patient_leads.primary_insurance_id')
            ->whereDate('patient_leads.created_at', '>=', $start->toDateString())
            ->whereDate('patient_leads.created_at', '<=', $end->toDateString())
            ->whereNull('patient_leads.patient_id')
            ->get()
            ->each(function ($patient) use (&$data) {
                $data[] = [
                    'Patient ID' => $patient->id,
                    'Patient Name' => $patient->patient_name,
                    'Patient Status' => 'Lead (Patient chart not created)',
                    'Insurance' => $patient->insurance,
                    'Patient Created At' => $patient->patient_created_at,
                    'Patient Returned At' => '',
                    'Providers' => '',
                    'First Visit Date' => '',
                    'Last Visit Date' => '',
                    'Visits Count' => 0,
                    'EHR Link' => '',
                ];
            });

        $patientInquiryQuery = PatientInquiry::select([
            'patients.id',
            \DB::raw("CONCAT(`patients`.`first_name`, ' ', `patients`.`last_name`) AS patient_name"),
            'patient_statuses.status',
            'patient_insurances.insurance',
            \DB::raw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) as patient_created_at"),
            \DB::raw("DATE(`patient_inquiries`.`created_at`) as patient_returned_at"),
        ])
            ->wherePatientIsCreated()
            ->where('patient_inquiries.is_returning', 1)
            ->join('patients', 'patients.id', '=', 'patient_inquiries.inquirable_id')
            ->leftJoin('patient_statuses', 'patient_statuses.id', '=', 'patients.status_id')
            ->leftJoin('patient_insurances', 'patient_insurances.id', '=', 'patients.primary_insurance_id')
            ->whereDate('patient_inquiries.created_at', '>=', $start->toDateString())
            ->whereDate('patient_inquiries.created_at', '<=', $end->toDateString())
            ->where('patients.is_test', 0)
            ->get()
            ->each(function ($patient) use (&$data) {
                $data[] = [
                    'Patient ID' => $patient->id,
                    'Patient Name' => $patient->patient_name,
                    'Patient Status' => $patient->status,
                    'Insurance' => $patient->insurance,
                    'Patient Created At' => $patient->patient_created_at,
                    'Patient Returned At' => $patient->patient_returned_at,
                    'Providers' => $this->getPatientProviders($patient->id),
                    'First Visit Date' => $this->getFirstVisitDate($patient->id),
                    'Last Visit Date' => $this->getLastVisitDate($patient->id),
                    'Visits Count' => $this->getVisitsCount($patient->id),
                    'EHR Link' => "https://admin.cwr.care/chart/{$patient->id}",
                ];
            });

        return [
            'title' => 'Total # of patients',
            'columnNames' => self::TOTAL_PTS_TITLES,
            'data' => $data
        ];
    }

    public function prepareNoChartPtsStatistics($start, $end)
    {
        $data = [];

        $patientLeadQuery = PatientLead::select([
            'patient_leads.id',
            \DB::raw("CONCAT(`patient_leads`.`first_name`, ' ', `patient_leads`.`last_name`) AS patient_name"),
            'patient_insurances.insurance',
            'patient_leads.created_at AS patient_created_at'
        ])
            ->leftJoin('patient_insurances', 'patient_insurances.id', '=', 'patient_leads.primary_insurance_id')
            ->whereDate('patient_leads.created_at', '>=', $start->toDateString())
            ->whereDate('patient_leads.created_at', '<=', $end->toDateString())
            ->whereNull('patient_leads.patient_id')
            ->get()
            ->each(function ($patient) use (&$data) {
                $data[] = [
                    'Patient ID' => $patient->id,
                    'Patient Name' => $patient->patient_name,
                    'Patient Status' => 'Lead (Patient chart not created)',
                    'Providers' => $this->getPatientProviders($patient->id),
                    'Insurance' => $patient->insurance,
                    'Patient Created At' => $patient->patient_created_at,
                ]; 
            });

        return [
            'title' => 'Inquiry only no chart created',
            'columnNames' => self::NO_CHART_PTS_TITLES,
            'data' => $data
        ];
    }

    public function prepareReturningPtsStatistics($start, $end)
    {
        $data = [];

        $patientInquiryQuery = PatientInquiry::select([
            'patients.id',
            \DB::raw("CONCAT(`patients`.`first_name`, ' ', `patients`.`last_name`) AS patient_name"),
            'patient_statuses.status',
            'patient_insurances.insurance',
            \DB::raw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) as patient_created_at"),
            \DB::raw("DATE(`patient_inquiries`.`created_at`) as patient_returned_at"),
        ])
            ->wherePatientIsCreated()
            ->where('patient_inquiries.is_returning', 1)
            ->join('patients', 'patients.id', '=', 'patient_inquiries.inquirable_id')
            ->leftJoin('patient_statuses', 'patient_statuses.id', '=', 'patients.status_id')
            ->leftJoin('patient_insurances', 'patient_insurances.id', '=', 'patients.primary_insurance_id')
            ->whereDate('patient_inquiries.created_at', '>=', $start->toDateString())
            ->whereDate('patient_inquiries.created_at', '<=', $end->toDateString())
            ->where('patients.is_test', 0)
            ->get()
            ->each(function ($patient) use (&$data) {
                $data[] = [
                    'Patient ID' => $patient->id,
                    'Patient Name' => $patient->patient_name,
                    'Patient Status' => $patient->status,
                    'Insurance' => $patient->insurance,
                    'Patient Created At' => $patient->patient_created_at,
                    'Patient Returned At' => $patient->patient_returned_at,
                    'Providers' => $this->getPatientProviders($patient->id),
                    'First Visit Date' => $this->getFirstVisitDate($patient->id),
                    'Last Visit Date' => $this->getLastVisitDate($patient->id),
                    'Visits Count' => $this->getVisitsCount($patient->id),
                    'EHR Link' => "https://admin.cwr.care/chart/{$patient->id}",
                ];
            });

        return [
            'title' => 'Returning patients',
            'columnNames' => self::RETURNING_PTS_TITLES,
            'data' => $data
        ];
    }

    public function prepareNoApptsPtsStatistics($start, $end)
    {
        $data = [];

        $patientQuery = Patient::select([
            'patients.id',
            \DB::raw("CONCAT(`patients`.`first_name`, ' ', `patients`.`last_name`) AS patient_name"),
            'patient_statuses.status',
            'patient_insurances.insurance',
            \DB::raw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) as patient_created_at"),
        ])
            ->leftJoin('patient_statuses', 'patient_statuses.id', '=', 'patients.status_id')
            ->leftJoin('patient_insurances', 'patient_insurances.id', '=', 'patients.primary_insurance_id')
            ->leftJoin('appointments', function($join) {
                $join->on($join->table . '.patients_id', '=', 'patients.id')
                    ->whereNull($join->table . '.deleted_at');
            })
            ->whereNull('appointments.id')
            ->whereRaw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) >= DATE('{$start->toDateString()}')")
            ->whereRaw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) <= DATE('{$end->toDateString()}')")
            ->where('patients.is_test', 0)
            ->get()
            ->each(function ($patient) use (&$data) {
                $data[] = [
                    'Patient ID' => $patient->id,
                    'Patient Name' => $patient->patient_name,
                    'Patient Status' => $patient->status,
                    'Providers' => $this->getPatientProviders($patient->id),
                    'Insurance' => $patient->insurance,
                    'Patient Created At' => $patient->patient_created_at,
                    'EHR Link' => "https://admin.cwr.care/chart/{$patient->id}",
                ]; 
            });

        return [
            'title' => 'Chart created but no appt',
            'columnNames' => self::NO_APPTS_PTS_TITLES,
            'data' => $data
        ];
    }

    public function prepareNoVisitsPtsStatistics($start, $end)
    {
        $data = [];

        $patientQuery = Patient::select([
            'patients.id',
            \DB::raw("CONCAT(`patients`.`first_name`, ' ', `patients`.`last_name`) AS patient_name"),
            'patient_statuses.status',
            'patient_insurances.insurance',
            \DB::raw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) as patient_created_at"),
        ])
            ->selectRaw('(SELECT COUNT(`appointments`.`id`) FROM appointments WHERE `appointments`.`patients_id` = `patients`.`id` AND `appointments`.`appointment_statuses_id` = 1 AND `appointments`.`deleted_at` IS NULL) AS visits_count')
            ->selectRaw('(SELECT COUNT(`appointments`.`id`) FROM appointments WHERE `appointments`.`patients_id` = `patients`.`id` AND `appointments`.`appointment_statuses_id` != 1 AND `appointments`.`deleted_at` IS NULL) AS appt_count')
            ->havingRaw('visits_count = 0')
            ->havingRaw('appt_count > 0')
            ->leftJoin('patient_statuses', 'patient_statuses.id', '=', 'patients.status_id')
            ->leftJoin('patient_insurances', 'patient_insurances.id', '=', 'patients.primary_insurance_id')
            ->whereRaw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) >= DATE('{$start->toDateString()}')")
            ->whereRaw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) <= DATE('{$end->toDateString()}')")
            ->where('patients.is_test', 0)
            ->get()
            ->each(function ($patient) use (&$data) {
                $data[] = [
                    'Patient ID' => $patient->id,
                    'Patient Name' => $patient->patient_name,
                    'Patient Status' => $patient->status,
                    'Insurance' => $patient->insurance,
                    'Patient Created At' => $patient->patient_created_at,
                    'Providers' => $this->getPatientProviders($patient->id),
                    'EHR Link' => "https://admin.cwr.care/chart/{$patient->id}",
                ]; 
            });

        return [
            'title' => '1 or more appts but no visits',
            'columnNames' => self::NO_VISITS_PTS_TITLES,
            'data' => $data
        ];
    }

    public function prepareOneVisitPtsStatistics($start, $end)
    {
        $data = [];

        $patientQuery = Patient::select([
            'patients.id',
            \DB::raw("CONCAT(`patients`.`first_name`, ' ', `patients`.`last_name`) AS patient_name"),
            'patient_statuses.status',
            'patient_insurances.insurance',
            \DB::raw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) as patient_created_at"),
        ])
            ->selectRaw('(SELECT COUNT(`appointments`.`id`) FROM appointments WHERE `appointments`.`patients_id` = `patients`.`id` AND `appointments`.`appointment_statuses_id` = 1 AND `appointments`.`deleted_at` IS NULL) AS visits_count')
            ->havingRaw('visits_count = 1')
            ->leftJoin('patient_statuses', 'patient_statuses.id', '=', 'patients.status_id')
            ->leftJoin('patient_insurances', 'patient_insurances.id', '=', 'patients.primary_insurance_id')
            ->whereRaw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) >= DATE('{$start->toDateString()}')")
            ->whereRaw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) <= DATE('{$end->toDateString()}')")
            ->where('patients.is_test', 0)
            ->get()
            ->each(function ($patient) use (&$data) {
                $data[] = [
                    'Patient ID' => $patient->id,
                    'Patient Name' => $patient->patient_name,
                    'Patient Status' => $patient->status,
                    'Insurance' => $patient->insurance,
                    'Patient Created At' => $patient->patient_created_at,
                    'Providers' => $this->getPatientProviders($patient->id),
                    'First Visit Date' => $this->getFirstVisitDate($patient->id),
                    'Last Visit Date' => $this->getLastVisitDate($patient->id),
                    'Visits Count' => 1,
                    'EHR Link' => "https://admin.cwr.care/chart/{$patient->id}",
                ]; 
            });

        return [
            'title' => '1 visit only',
            'columnNames' => self::DEFAULT_PTS_TITLES,
            'data' => $data
        ];
    }

    public function prepareFewVisitsPtsStatistics($start, $end)
    {
        $data = [];

        $patientQuery = Patient::select([
            'patients.id',
            \DB::raw("CONCAT(`patients`.`first_name`, ' ', `patients`.`last_name`) AS patient_name"),
            'patient_statuses.status',
            'patient_insurances.insurance',
            \DB::raw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) as patient_created_at"),
        ])
            ->selectRaw('(SELECT COUNT(`appointments`.`id`) FROM appointments WHERE `appointments`.`patients_id` = `patients`.`id` AND `appointments`.`appointment_statuses_id` = 1 AND `appointments`.`deleted_at` IS NULL) AS visits_count')
            ->havingRaw('visits_count > 1')
            ->leftJoin('patient_statuses', 'patient_statuses.id', '=', 'patients.status_id')
            ->leftJoin('patient_insurances', 'patient_insurances.id', '=', 'patients.primary_insurance_id')
            ->whereRaw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) >= DATE('{$start->toDateString()}')")
            ->whereRaw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) <= DATE('{$end->toDateString()}')")
            ->where('patients.is_test', 0)
            ->get()
            ->each(function ($patient) use (&$data) {
                $data[] = [
                    'Patient ID' => $patient->id,
                    'Patient Name' => $patient->patient_name,
                    'Patient Status' => $patient->status,
                    'Insurance' => $patient->insurance,
                    'Patient Created At' => $patient->patient_created_at,
                    'Providers' => $this->getPatientProviders($patient->id),
                    'First Visit Date' => $this->getFirstVisitDate($patient->id),
                    'Last Visit Date' => $this->getLastVisitDate($patient->id),
                    'Visits Count' => $this->getVisitsCount($patient->id),
                    'EHR Link' => "https://admin.cwr.care/chart/{$patient->id}",
                ]; 
            });

        return [
            'title' => '2 or more visits',
            'columnNames' => self::DEFAULT_PTS_TITLES,
            'data' => $data
        ];
    }

    public function prepareActivePtsCountStatistics($start, $end)
    {
        $data = [];

        $patientQuery = Patient::select([
            'patients.id',
            \DB::raw("CONCAT(`patients`.`first_name`, ' ', `patients`.`last_name`) AS patient_name"),
            'patient_statuses.status',
            'patient_insurances.insurance',
            \DB::raw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) as patient_created_at"),
        ])
            ->leftJoin('patient_statuses', 'patient_statuses.id', '=', 'patients.status_id')
            ->leftJoin('patient_insurances', 'patient_insurances.id', '=', 'patients.primary_insurance_id')
            ->where('patients.status_id', 1)
            ->whereRaw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) >= DATE('{$start->toDateString()}')")
            ->whereRaw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) <= DATE('{$end->toDateString()}')")
            ->where('patients.is_test', 0)
            ->get()
            ->each(function ($patient) use (&$data) {
                $data[] = [
                    'Patient ID' => $patient->id,
                    'Patient Name' => $patient->patient_name,
                    'Patient Status' => $patient->status,
                    'Insurance' => $patient->insurance,
                    'Patient Created At' => $patient->patient_created_at,
                    'Providers' => $this->getPatientProviders($patient->id),
                    'First Visit Date' => $this->getFirstVisitDate($patient->id),
                    'Last Visit Date' => $this->getLastVisitDate($patient->id),
                    'Visits Count' => $this->getVisitsCount($patient->id),
                    'EHR Link' => "https://admin.cwr.care/chart/{$patient->id}",
                ]; 
            });

        return [
            'title' => 'Patients still active today',
            'columnNames' => self::DEFAULT_PTS_TITLES,
            'data' => $data
        ];
    }

    private function getPatientProviders($patientId)
    {
        return Appointment::query()
            ->select(['providers.provider_name', \DB::raw('DATE(FROM_UNIXTIME(MIN(`appointments`.`time`))) AS appt_date')])
            ->join('providers', 'providers.id', '=', 'appointments.providers_id')
            ->where('appointments.patients_id', '=', $patientId)
            ->where('providers.is_test', '=', 0)
            ->groupBy('appointments.providers_id')
            ->orderBy('appointments.time')
            ->get()
            ->reduce(function ($carry, $item) {
                if ($carry) {
                    $carry = $carry . '; ';
                }
                $carry = $carry . "{$item->provider_name} (DOS: {$item->appt_date})";
    
                return $carry;
            }, '');
    }

    private function getFirstVisitDate($patientId)
    {
        return Appointment::query()
            ->selectRaw('DATE(FROM_UNIXTIME(MIN(`appointments`.`time`))) AS first_visit_date')
            ->where('appointments.patients_id', '=', $patientId)
            ->where('appointments.appointment_statuses_id', '=', 1)
            ->groupBy('appointments.patients_id')
            ->first()['first_visit_date'];
    }

    private function getLastVisitDate($patientId)
    {
        return Appointment::query()
            ->selectRaw('DATE(FROM_UNIXTIME(MAX(`appointments`.`time`))) AS last_visit_date')
            ->where('appointments.patients_id', '=', $patientId)
            ->where('appointments.appointment_statuses_id', '=', 1)
            ->groupBy('appointments.patients_id')
            ->first()['last_visit_date'];
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
}

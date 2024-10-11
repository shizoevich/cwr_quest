<?php

namespace App\Console\Commands\StatisticsReport;

use App\Provider;
use App\Status;
use App\Appointment;
use App\Models\Billing\BillingPeriodType;
use App\Helpers\AvailabilityHelper;
use App\Components\Availability\ProviderWorkHours;
use Carbon\Carbon;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateTherapistsPlanCompletionReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics-report:therapists-plan-completion';

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
        $startDate = Carbon::today()->startOfYear()->startOfWeek();
        $endDate = $startDate->copy()->addWeek(12)->endOfWeek();
        $data = $this->getData($startDate, $endDate);

        $this->generateExcelReport($data, $startDate, $endDate);
    }

    private function getData(Carbon $startDate, Carbon $endDate)
    {
        $weeksCount = AvailabilityHelper::getWeeksCount($startDate, $endDate);

        $workHoursHelper = new ProviderWorkHours($startDate, $endDate, true, true, true, null, true);
        $totalAvailabilityMapping = $workHoursHelper->getTotalMapping();

        $firstWeekEndDate = $startDate->copy()->endOfWeek();

        return Provider::query()
            ->select([
                'providers.id',
                'providers.provider_name',
                'providers.work_hours_per_week',
                'tariffs_plans.name AS tariff_plan_name',
                \DB::raw("(SELECT DATE(FROM_UNIXTIME(MIN(`appointments`.`time`))) FROM appointments WHERE appointments.providers_id = providers.id AND appointments.appointment_statuses_id = 1) AS first_visit_date")
            ])
            ->join('providers_tariffs_plans', 'providers_tariffs_plans.provider_id', '=', 'providers.id')
            ->join('tariffs_plans', 'tariffs_plans.id', '=', 'providers_tariffs_plans.tariff_plan_id')
            ->where('providers.is_test', 0)
            ->where('providers.billing_period_type_id', BillingPeriodType::getBiWeekly()->id)
            ->havingRaw("first_visit_date <= DATE('{$firstWeekEndDate->toDateString()}')")
            ->get()
            ->map(function ($provider) use ($weeksCount, &$totalAvailabilityMapping, &$startDate, &$endDate) {
                $totalAvailability = $totalAvailabilityMapping[$provider->id] ?? [];

                $activeAppts = $totalAvailability['activeAppointmentsCount'] ?? 0;
                $visits = ($totalAvailability['completedAppointmentsCount'] ?? 0) + ($totalAvailability['visitCreatedAppointmentsCount'] ?? 0);
                $visitsWithoutPn = $this->getVisitsWithMissingNotesCount($provider->id, $startDate, $endDate);
                $visitsWithPn = $visits - $visitsWithoutPn;
                $cancelledAppts = $totalAvailability['canceledAppointmentsCount'] ?? 0;
                $remainingAvailability = $totalAvailability['forApptsRemainingAvailabilityLength'] ?? 0;
                $remainingAvailability = floor($remainingAvailability / 60);
                $totalAppts = $activeAppts + $visits + $cancelledAppts;
                $total = $totalAppts + $remainingAvailability;

                $cancelledByPatient = $totalAvailability['cancelledByPatientAppointmentsCount'] ?? 0;
                $cancelledByProvider = $totalAvailability['cancelledByProviderAppointmentsCount'] ?? 0;
                $lastMinuteCancel = $totalAvailability['lastMinuteCancelByPatientAppointmentsCount'] ?? 0;
                $patientDidNotCome = $totalAvailability['patientDidNotComeAppointmentsCount'] ?? 0;
                $cancelledByOffice = $totalAvailability['cancelledByOfficeAppointmentsCount'] ?? 0;

                return [
                    'Provider Name' => $provider->provider_name,
                    'Tariff Plan' => $provider->tariff_plan_name,
                    'Work Hours Per Week' => $provider->work_hours_per_week,
                    'Weeks Count' => $weeksCount,
                    'Active Appts. Count' => strval($activeAppts),
                    'Visits Count' => strval($visits),
                    'Cancelled Appts. Count' => strval($cancelledAppts),
                    'Remaining Availability' => strval($remainingAvailability),
                    'Total Count' => strval($total),
                    'Visit Without P.N. Count' => strval($visitsWithoutPn),
                    'Visit With P.N. Count' => strval($visitsWithPn),
                    'Cancelled By Patient Appts. Count' => strval($cancelledByPatient),
                    'Cancelled By Patient Appts. Percent' => $totalAppts > 0 ? strval($cancelledByPatient / $totalAppts) : '0',
                    'Cancelled By Provider Appts. Count' => strval($cancelledByProvider),
                    'Cancelled By Provider Appts. Percent' => $totalAppts > 0 ? strval($cancelledByProvider / $totalAppts) : '0',
                    'Last Minute Cancel By Patient Appts. Count' => strval($lastMinuteCancel),
                    'Last Minute Cancel By Patient Appts. Percent' => $totalAppts > 0 ? strval($lastMinuteCancel / $totalAppts) : '0',
                    'Patient Did Not Come Appts. Count' => strval($patientDidNotCome),
                    'Patient Did Not Come Appts. Percent' => $totalAppts > 0 ? strval($patientDidNotCome / $totalAppts) : '0',
                    'Cancelled By Office Appts. Count' => strval($cancelledByOffice),
                    'Cancelled By Office Appts. Percent' => $totalAppts > 0 ? strval($cancelledByOffice / $totalAppts) : '0',
                ];
            })
            ->toArray();
    }

    private function getVisitsWithMissingNotesCount($providerId, Carbon $startDate, Carbon $endDate)
    {
        $appointmentStatuses = Status::getCompletedVisitCreatedStatusesId();
        
        return Appointment::query()
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->leftJoin('patient_notes', function($join) {
                $join->on($join->table . '.appointment_id', '=', 'appointments.id')
                    ->whereNull($join->table . '.deleted_at');
            })
            ->where('appointments.providers_id', '=', $providerId)
            ->whereIn('appointments.appointment_statuses_id', $appointmentStatuses)
            ->where('patients.is_test', '=', 0)
            ->where('appointments.note_on_paper', '=', 0)
            ->where('appointments.is_initial', '=', 0)
            ->whereNull('appointments.initial_assessment_id')
            ->where(function($query) {
                $query->whereNull('patient_notes.id')->orWhere('patient_notes.is_finalized', false);
            })
            ->whereBetween('time', [$startDate->timestamp, $endDate->timestamp])
            ->count();
    }

    private function generateExcelReport($data, Carbon $startDate, Carbon $endDate)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $titles = [
            'Provider Name',
            'Tariff Plan',
            'Work Hours Per Week',
            'Weeks Count',
            'Active Appts. Count',
            'Visits Count',
            'Cancelled Appts. Count',
            'Remaining Availability',
            'Total Count',
            'Visit Without P.N. Count',
            'Visit With P.N. Count',
            'Cancelled By Patient Appts. Count',
            'Cancelled By Patient Appts. Percent',
            'Cancelled By Provider Appts. Count',
            'Cancelled By Provider Appts. Percent',
            'Last Minute Cancel By Patient Appts. Count',
            'Last Minute Cancel By Patient Appts. Percent',
            'Patient Did Not Come Appts. Count',
            'Patient Did Not Come Appts. Percent',
            'Cancelled By Office Appts. Count',
            'Cancelled By Office Appts. Percent',
        ];

        $worksheet->fromArray([$titles], NULL, 'A1');
        $worksheet->fromArray($data, NULL, 'A2');

        $writer = new Xlsx($spreadsheet);
        $filename = "therapists_plan_completion_{$startDate->toDateString()}__{$endDate->toDateString()}.xlsx";
        $path = storage_path('app/temp/' . $filename);
        $writer->save($path);
    }
}

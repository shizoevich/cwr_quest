<?php

namespace App\Http\Controllers\Dashboard;

use App\Components\Salary\Salary;
use App\Components\Availability\ProviderWorkHours;
use App\Helpers\SalaryReportHelper;
use App\Http\Requests\Visits\SyncVisits;
use App\Models\Billing\BillingPeriod;
use App\Models\Billing\BillingPeriodType;
use App\Option;
use App\Provider;
use App\Services\Salary\SalaryQuotaService;
use App\Status;
use App\Repositories\Provider\Salary\BillingPeriodRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Provider\Salary as ProviderSalary;
use App\Models\Provider\SalaryTimesheet;
use App\Models\Provider\SalaryTimesheetVisit;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Traits\Providers\ProviderSummaryTrait;

class ProviderSalaryController extends Controller
{
    use ProviderSummaryTrait;

    private $salaryQuotaService;

    public function __construct(SalaryQuotaService $salaryQuotaService)
    {
        $this->salaryQuotaService = $salaryQuotaService;
    }

    private function getDatesFromRequest(array $requestData)
    {
        $billingPeriod = null;
        switch (data_get($requestData, 'selected_filter_type')) {
            case 1:
                //filter by date
                $startDate = Carbon::parse(data_get($requestData, 'date_from'))->startOfDay();
                $endDate = $startDate->copy()->endOfDay();
                break;
            case 2:
                //filter by date range
                $startDate = Carbon::parse(data_get($requestData, 'date_from'))->startOfDay();
                $endDate = Carbon::parse(data_get($requestData, 'date_to'))->endOfDay();
                break;
            case 3:
                //filter by month
                $startDate = Carbon::createFromFormat('d F Y', data_get($requestData, 'month'))->startOfMonth();
                $endDate = $startDate->copy()->endOfMonth();
                break;
            case 4:
            case 5:
                //filter by billing period
                $billingPeriod = BillingPeriod::findOrFail(data_get($requestData, 'billing_period_id'));
                $startDate = Carbon::parse($billingPeriod->start_date)->startOfDay();
                $endDate = Carbon::parse($billingPeriod->end_date)->endOfDay();
                break;
            default:
                //filter by billing period
                $billingPeriod = BillingPeriod::query()
                    ->where('start_date', '<=', Carbon::now()->toDateString())
                    ->orderByDesc('start_date')
                    ->where('type_id', BillingPeriodType::getBiWeekly()->getKey())
                    ->first();
                $startDate = Carbon::parse($billingPeriod->start_date)->startOfDay();
                $endDate = Carbon::parse($billingPeriod->end_date)->endOfDay();
        }
        
        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'billing_period' => $billingPeriod,
        ];
    }
    
    /**
     * @param Request  $request
     * @param Provider $provider
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function details(Request $request, Provider $provider)
    {
        $dates = $this->getDatesFromRequest($request->all());
        $salaryHelper = new Salary($dates['start_date'], $dates['end_date'], $provider->id, $dates['billing_period']);
        $details = $salaryHelper->getDetails()->toArray();
        
        return response()->json([
            'details' => __data_get($details, 'regular.' . $provider->id, []),
            'progress_note_details' => __data_get($details, 'missing_progress_notes.' . $provider->id, []),
            'refunds_for_progress_note_details' => __data_get($details, 'refunds_for_completed_progress_notes.' . $provider->id, []),
            'additional_compensation' => __data_get($details, 'additional_compensation.' . $provider->id, []),
            'late_cancellations' => __data_get($details, 'late_cancellations.' . $provider->id, []),
        ]);
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if (Auth::user()->isSecretary()) {
            abort(403);
        }

        $providers = Provider::query()->orderBy('provider_name')->get();
        $billingPeriods = app()->make(BillingPeriodRepositoryInterface::class)->all();

        if ($request->input('provider')) {
            $selectedProvider = $request->input('provider');
        } else {
            $selectedProvider = null;
        }
        if ($request->filled('month')) {
            $month = Carbon::createFromFormat('d F Y', $request->month)->format('d F Y');
        } else {
            $month = Carbon::now()->startOfMonth()->format('d F Y');
        }

        $dates = $this->getDatesFromRequest($request->all());
        $salaryHelper = new Salary($dates['start_date'], $dates['end_date'], $selectedProvider, $dates['billing_period']);
        $salary = $salaryHelper->get()->toArray();
        $regularSalary = __data_get($salary, 'regular', []);
        $missingNotesSalary = __data_get($salary, 'missing_progress_notes', []);
        $refundsSalary = __data_get($salary, 'refunds_for_completed_progress_notes', []);
        $additionalSalary = __data_get($salary, 'additional_compensation', []);

        $salaryHelperForYear = new Salary(Carbon::now()->subYear(), Carbon::now(), $selectedProvider);
        $salaryForYear = $salaryHelperForYear->get()->toArray();
        $regularSalaryForYear = __data_get($salaryForYear, 'regular', []);
        $missingNotesSalaryForYear = __data_get($salaryForYear, 'missing_progress_notes', []);

        $complaintTimesheetData = SalaryTimesheet::query()
            ->select(['provider_id', 'billing_period_id', 'complaint', 'is_resolve_complaint'])
            ->where('billing_period_id', $dates['billing_period']['id'])
            ->get();

        $totalWorkedYearsMapping = $this->getTotalWorkedYearsMapping($providers);

        $startOfYear = $dates['start_date']->copy()->startOfYear();
        $endOfYear = $startOfYear->copy()->endOfYear();
        $sickTimeMapping = $this->getSickTimeMapping($startOfYear, $endOfYear);
        $appointmentsCountMapping = $this->getAppointmentsPerYearCountMapping($startOfYear, $endOfYear);
        $workHoursHelper = new ProviderWorkHours($dates['start_date'], $dates['end_date'], true, true, is_null($selectedProvider), $selectedProvider);
        $totalAvailabilityMapping = $workHoursHelper->getTotalMapping();

        return view('dashboard.salary.index', [
            'providers' => $providers,
            'billingPeriods' => $billingPeriods,
            'selectedProvider' => $selectedProvider,
            'salary' => $regularSalary,
            'missingNotes' => $missingNotesSalary,
            'refundsForMissingNotes' => $refundsSalary,
            'additionalCompensation' => $additionalSalary,
            'salaryTotal' => Salary::getSalaryTotalMapping($regularSalary),
            'missingNotesTotal' => Salary::getMissingNotesTotalMapping($missingNotesSalary),
            'salaryForYearTotal' => Salary::getSalaryTotalMapping($regularSalaryForYear),
            'missingNotesForYearTotal' => Salary::getSalaryTotalMapping($missingNotesSalaryForYear),
            'dateFrom' => $request->date_from,
            'dateTo' => $request->date_to,
            'month' => $month,
            'billingPeriodId' => $request->input('billing_period_id') ?? data_get($billingPeriods, 'bi_weekly.0.id'),
            'selectedFilterType' => $request->selected_filter_type ?? 4,
            'isParserRunning' => intval(Option::getOptionValue('parsing_visits')),
            'complaints' =>  $complaintTimesheetData,
            'totalWorkedYearsMapping' =>  $totalWorkedYearsMapping,
            'sickTimeMapping' => $sickTimeMapping,
            'appointmentsCountMapping' => $appointmentsCountMapping,
            'totalAvailabilityMapping' => $totalAvailabilityMapping,
        ]);
    }
    
    public function checkVisitsParser() {
        return response([
            'status' => Option::getOptionValue('parsing_visits')
        ]);
    }
    
    public function syncVisits(SyncVisits $request) {
        if(Option::getOptionValue('parsing_visits')) {
            return response(['message' => 'Parser is already running.'], 405);
        }
        $queueName = 'visits-parser';
        $errors = [];
        $payload = [];
        switch($request->sync_by) {
            case 'date':
                if(is_null($request->sync_date)) {
                    $errors[] = 'The sync date cannot be null.';
                }
                $payload = [
                    'date' => $request->sync_date,
                ];
                break;
            case 'date_range':
                if(is_null($request->sync_start_date)) {
                    $errors[] = 'The sync start date cannot be null.';
                }
                if(is_null($request->sync_end_date)) {
                    $errors[] = 'The sync end date cannot be null.';
                }
                $payload = [
                    'start_date' => $request->sync_start_date,
                    'end_date' => $request->sync_end_date,
                ];
                break;
            case 'month':
                if(is_null($request->sync_month)) {
                    $errors[] = 'The sync month cannot be null.';
                }
                $payload = [
                    'month' => $request->sync_month,
                ];
                break;
            case 'therapist':
                if(is_null($request->sync_provider)) {
                    $errors[] = 'The sync provider cannot be null.';
                }
                $payload = [
                    'office_ally_provider_id' => $request->sync_provider,
                ];
                break;
            case 'visit':
                if(is_null($request->sync_visit)) {
                    $errors[] = 'The sync visit cannot be null.';
                }
                $payload = [
                    'visit_id' => $request->sync_visit, 
                ];
                break;
        }
        
        if(count($errors)) {
            if($request->expectsJson()) {
                return response($errors, 422);
            }
            
            redirect()->back()->withErrors($errors);
        }
        Option::setOptionValue('parsing_visits', 1);
        $job = (new \App\Jobs\Parsers\Guzzle\PatientVisitsParser($payload, true))->onQueue($queueName);
        
        dispatch($job);
        
        if($request->expectsJson()) {
            return response(['success' => true,]);
        }
        
        return redirect()->back();
    }
    
    public function download(Request $request, $id) {
        if(Auth::user()->isSecretary()) {
            abort(403);
        }
        $provider = Provider::findOrFail($id);
        $dates = $this->getDatesFromRequest($request->all());
        $salaryHelper = new Salary($dates['start_date'], $dates['end_date'], $id, $dates['billing_period']);
        $allSalary = $salaryHelper->getForReport();
        $salary = [
            'regular' => __data_get($allSalary, 'regular.' . $provider->id, []),
            'missing_progress_notes' => __data_get($allSalary, 'missing_progress_notes.' . $provider->id, []),
            'refunds_for_completed_progress_notes' => __data_get($allSalary, 'refunds_for_completed_progress_notes.' . $provider->id, []),
            'additional_compensation' => __data_get($allSalary, 'additional_compensation.' . $provider->id, []),
        ];
        $allSalaryDetails = $salaryHelper->getDetailsForReport(false);
        $salaryDetails = [
            'regular' => __data_get($allSalaryDetails, 'regular.' . $provider->id, []),
            'missing_progress_notes' => __data_get($allSalaryDetails, 'missing_progress_notes.' . $provider->id, []),
            'refunds_for_completed_progress_notes' => __data_get($allSalaryDetails, 'refunds_for_completed_progress_notes.' . $provider->id, []),
        ];
        $longSalaryDetails = $salaryHelper->getDetails()->toArray();
        $longSalaryDetails = [
            'regular' => __data_get($longSalaryDetails, 'regular.' . $provider->id, []),
            'missing_progress_notes' => __data_get($longSalaryDetails, 'missing_progress_notes.' . $provider->id, []),
            'refunds_for_completed_progress_notes' => __data_get($longSalaryDetails, 'refunds_for_completed_progress_notes.' . $provider->id, []),
            'additional_compensation' => __data_get($longSalaryDetails, 'additional_compensation.' . $provider->id, []),
            'late_cancellations' => __data_get($allSalaryDetails, 'late_cancellation.' . $provider->id, []),
        ];

        return with(new SalaryReportHelper)->generate($provider, $salary, $salaryDetails, $longSalaryDetails, $request->all());
    }
    
    public function complaintReviewed(Request $request){

      $data = $request->all();

      SalaryTimesheet::where('billing_period_id', $data['billing_period_id'])
                     ->where('provider_id', $data['provider_id'])
                     ->update(['is_resolve_complaint' => (boolean)$data['is_resolve_complaint']]);
    }


    public function getProvidersForSalaryQuota()
    {
        $data = $this->salaryQuotaService->getProvidersForSalaryQuota();

        return response()->json(['providers' => $data]);
    }

    public function calculateSalary(Request $request)
    {
        $data = $this->salaryQuotaService->calculateSalary($request->all());

        return response()->json($data);
    }
}

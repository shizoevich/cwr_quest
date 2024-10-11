<?php

namespace App\Http\Controllers\Api\System;

use App\Http\Controllers\Controller;
use App\Models\Billing\BillingPeriod;
use App\PatientInsurance;
use App\Repositories\Provider\Salary\BillingPeriodRepositoryInterface;
use App\Repositories\Provider\Salary\Timesheet\TimesheetRepositoryInterface;
use Carbon\Carbon;

class BillingPeriodController extends Controller
{
    protected $billingPeriodRepository;
    protected $timesheetRepository;

    public function __construct(BillingPeriodRepositoryInterface $billingPeriodRepository, TimesheetRepositoryInterface $timesheetRepository)
    {
        $this->billingPeriodRepository = $billingPeriodRepository;
        $this->timesheetRepository = $timesheetRepository;
    }

    public function index()
    {
        return response()->json([
            'billing_periods' => $this->billingPeriodRepository->all(),
        ]);
    }
    
    public function previous()
    {
        $period = null;
        if(optional(auth()->user()->provider)->billingPeriodType) {
            $temp = BillingPeriod::getPrevious(auth()->user()->provider->billingPeriodType->name);
            // uncomment to block current period
            // $temp = BillingPeriod::query()
            //     ->select('billing_periods.*')
            //     ->join('billing_period_types', 'billing_period_types.id', 'billing_periods.type_id')
            //     ->whereDate('billing_periods.end_date', '<', Carbon::today()->subDays(5)->toDateString())
            //     ->where('billing_period_types.name', auth()->user()->provider->billingPeriodType->name)
            //     ->orderByDesc('billing_periods.end_date')
            //     ->first();

            $period = optional($temp)->load('type');
        }

        $provider = auth()->user()->provider;
        $timesheet = $this->timesheetRepository->getTimesheet($provider);

        $startOfYear = isset($period) ? Carbon::parse($period->start_date)->startOfYear() : Carbon::now()->startOfYear();
        $endOfYear = $startOfYear->copy()->endOfYear();
        $remainingSickHours = $this->timesheetRepository->getRemainingSickHours($provider, $startOfYear, $endOfYear);
        
        return response()->json([
            'billing_period' => $period,
            'timesheet' => $timesheet,

            'is_editing_allowed' => $this->timesheetRepository->isEditingAllowed($provider),
            // uncomment to block current period
            // 'is_editing_allowed' => false,

            'remaining_sick_hours' => $remainingSickHours,
        ]);
    }
}
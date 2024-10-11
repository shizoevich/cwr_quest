<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\Salary\Timesheet\Complete;
use App\Http\Requests\Provider\Salary\Timesheet\IndexLateCancellations;
use App\Http\Requests\Provider\Salary\Timesheet\IndexVisits;
use App\Http\Requests\Provider\Salary\Timesheet\ModifyLateCancellations;
use App\Http\Requests\Provider\Salary\Timesheet\ModifySupervisions;
use App\Http\Requests\Provider\Salary\Timesheet\ModifyVisits;
use App\Models\Billing\BillingPeriod;
use App\Models\Provider\SalaryTimesheetNotification;
use App\Repositories\Provider\ProviderRepositoryInterface;
use App\Repositories\Provider\Salary\Timesheet\TimesheetRepositoryInterface;
use Carbon\Carbon;

class SalaryTimesheetController extends Controller
{
    /**
     * @var TimesheetRepositoryInterface
     */
    private $timesheetRepository;
    
    /**
     * SalaryTimesheetController constructor.
     *
     * @param TimesheetRepositoryInterface $timesheetRepository
     */
    public function __construct(TimesheetRepositoryInterface $timesheetRepository)
    {
        $this->timesheetRepository = $timesheetRepository;
    }
    
    public function visits(IndexVisits $request)
    {
        $billingPeriod = BillingPeriod::getPrevious(auth()->user()->provider->billingPeriodType->name);
        // uncomment to block current period
        // $billingPeriod = BillingPeriod::query()
        //     ->select('billing_periods.*')
        //     ->join('billing_period_types', 'billing_period_types.id', 'billing_periods.type_id')
        //     ->whereDate('billing_periods.end_date', '<', Carbon::today()->subDays(5)->toDateString())
        //     ->where('billing_period_types.name', auth()->user()->provider->billingPeriodType->name)
        //     ->orderByDesc('billing_periods.end_date')
        //     ->first();

        return response()->json([
            'data' => array_values($this->timesheetRepository->visits(auth()->user()->provider)),
            'billing_period' => optional($billingPeriod)->load('type'),

            'is_editing_allowed' => $this->timesheetRepository->isEditingAllowed(auth()->user()->provider),
            // uncomment to block current period
            // 'is_editing_allowed' => false,
        ]);
    }
    
    public function modifyVisits(ModifyVisits $request)
    {
        $this->timesheetRepository->modifyVisits($request->all());
        
        return response()->json(null, 204);
    }

    public function lateCancellations(IndexLateCancellations $request)
    {
        $billingPeriod = BillingPeriod::getPrevious(auth()->user()->provider->billingPeriodType->name);
        // uncomment to block current period
        // $billingPeriod = BillingPeriod::query()
        //     ->select('billing_periods.*')
        //     ->join('billing_period_types', 'billing_period_types.id', 'billing_periods.type_id')
        //     ->whereDate('billing_periods.end_date', '<', Carbon::today()->subDays(5)->toDateString())
        //     ->where('billing_period_types.name', auth()->user()->provider->billingPeriodType->name)
        //     ->orderByDesc('billing_periods.end_date')
        //     ->first();

        return response()->json([
            'data' => array_values($this->timesheetRepository->lateCancellations(auth()->user()->provider)),
            'billing_period' => optional($billingPeriod)->load('type'),

            'is_editing_allowed' => $this->timesheetRepository->isEditingAllowed(auth()->user()->provider),
            // uncomment to block current period
            // 'is_editing_allowed' => false,
        ]);
    }
    
    public function modifyLateCancellations(ModifyLateCancellations $request)
    {
        $this->timesheetRepository->modifyLateCancellations($request->all());
        
        return response()->json(null, 204);
    }

    public function supervisions()
    {
        $billingPeriod = BillingPeriod::getPrevious(auth()->user()->provider->billingPeriodType->name);
        // uncomment to block current period
        // $billingPeriod = BillingPeriod::query()
        //     ->select('billing_periods.*')
        //     ->join('billing_period_types', 'billing_period_types.id', 'billing_periods.type_id')
        //     ->whereDate('billing_periods.end_date', '<', Carbon::today()->subDays(5)->toDateString())
        //     ->where('billing_period_types.name', auth()->user()->provider->billingPeriodType->name)
        //     ->orderByDesc('billing_periods.end_date')
        //     ->first();

        return response()->json([
            'data' => array_values($this->timesheetRepository->supervisions(auth()->user()->provider)),
            'billing_period' => optional($billingPeriod)->load('type'),

            'is_editing_allowed' => $this->timesheetRepository->isEditingAllowed(auth()->user()->provider),
            // uncomment to block current period
            // 'is_editing_allowed' => false,
        ]);
    }

    public function modifySupervisions(ModifySupervisions $request)
    {   
        $supervisions = $request->get('supervisions');
        if (isset($supervisions) && count($supervisions)) {
            $this->timesheetRepository->modifySupervisions($request->input('billing_period_id'), $supervisions);
        }
        
        return response()->json(null, 204);
    }
    
    public function complete(Complete $request)
    {
        $this->timesheetRepository->complete($request->all()); 
        
        return response()->json(null, 204);
    }
    
    public function checkConfirmation()
    {
        // uncomment to block current period
        // return response()->json([
        //     'confirmed' => true,
        // ]);

        if(auth()->user()->isInsuranceAudit()) {
            return response()->json([
                'confirmed' => true,
            ]);
        }

        $provider = auth()->user()->provider;
        $providerRepository = app()->make(ProviderRepositoryInterface::class);
        $isBiWeeklyType = $providerRepository->billingPeriodName($provider) === 'bi_weekly';
        if (!$isBiWeeklyType) {
            return response()->json([
                'confirmed' => true,
            ]);
        }
        
        if (!$provider->billingPeriodType) {
            return response()->json([
                'confirmed' => true,
            ]);
        }

        $prevBillingPeriod = BillingPeriod::getPrevious($provider->billingPeriodType->name);
        if (!$prevBillingPeriod) {
            return response()->json([
                'confirmed' => true,
            ]);
        }

        $notification = SalaryTimesheetNotification::where('provider_id', '=', $provider->id)
            ->where('billing_period_id', '=', $prevBillingPeriod->id)
            ->first();
        if (isset($notification)) {
            if (isset($notification->viewed_at)) {
                return response()->json([
                    'confirmed' => true,
                ]);
            }
            if (isset($notification->remind_after) && Carbon::parse($notification->remind_after)->gt(Carbon::now())) {
                return response()->json([
                    'confirmed' => true,
                ]);
            }
        }

        $timesheetAllowedEditingDate = Carbon::parse($prevBillingPeriod->end_date)->addDay()->startOfDay()->addHours(config('timesheet.allowed_editing_gap'));
        if (Carbon::now()->gt($timesheetAllowedEditingDate)) {
            $timesheet = $this->timesheetRepository->getTimesheet($provider, $prevBillingPeriod);

            return response()->json([
                'confirmed' => isset($timesheet) && isset($timesheet->signed_at),
                'submit_required_date' => Carbon::parse($prevBillingPeriod->end_date)->addDay()->startOfDay()->addHours(config('timesheet.submit_required_gap'))->format('m/d/Y g:i A')
            ]);
        }
        
        return response()->json([
            'confirmed' => true,
        ]);
    }

    public function markAsViewed()
    {
        $provider = auth()->user()->provider;
        if (!$provider->billingPeriodType) {
            return response()->json([
                'success' => false
            ]);
        }

        $prevBillingPeriod = BillingPeriod::getPrevious($provider->billingPeriodType->name);
        if (!$prevBillingPeriod) {
            return response()->json([
                'success' => false
            ]);
        }

        SalaryTimesheetNotification::updateOrCreate(['provider_id' => $provider->id, 'billing_period_id' => $prevBillingPeriod->id], [
            'viewed_at' => Carbon::now()
        ]);

        return response()->json([
            'success' => true
        ]);
    }

    public function remindLater()
    {
        $provider = auth()->user()->provider;
        if (!$provider->billingPeriodType) {
            return response()->json([
                'success' => false
            ]);
        }

        $prevBillingPeriod = BillingPeriod::getPrevious($provider->billingPeriodType->name);
        if (!$prevBillingPeriod) {
            return response()->json([
                'success' => false
            ]);
        }

        SalaryTimesheetNotification::updateOrCreate(['provider_id' => $provider->id, 'billing_period_id' => $prevBillingPeriod->id], [
            'remind_after' => Carbon::now()->addMinutes(config('timesheet.remind_later_gap'))
        ]);

        return response()->json([
            'success' => true
        ]);
    }
}

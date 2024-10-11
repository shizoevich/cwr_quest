<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Provider\Availability\ProviderAvailabilityRepositoryInterface;
use App\Repositories\Provider\Salary\BillingPeriodRepositoryInterface;
use App\Helpers\AvailabilityHelper;
use App\Provider;
use Carbon\Carbon;

class ProviderAvailabilityController extends Controller
{
    protected $providerAvailabilityRepository;
    protected $billingPeriodRepository;

    public function __construct(ProviderAvailabilityRepositoryInterface $providerAvailabilityRepository, BillingPeriodRepositoryInterface $billingPeriodRepository)
    {
        $this->providerAvailabilityRepository = $providerAvailabilityRepository;
        $this->billingPeriodRepository = $billingPeriodRepository;
    }

    public function providersWithTotalAvailability(Request $request)
    {
        $week = Carbon::now()->startOfWeek();
        if ($request->filled('week')) {
            $startDate = AvailabilityHelper::getWeekStartDate($request->week);
            if (isset($startDate)) {
                $week = $startDate;
            }
        }

        $billingPeriods = $this->billingPeriodRepository->all();
        $providers = $this->providerAvailabilityRepository->getProvidersWithTotalAvailability($request->all());

        return view('dashboard.statistics.provider-availability.index', [
            'week' => $week,
            'billingPeriods' => $billingPeriods,
            'billingPeriodId' => $request->input('billing_period_id') ?? data_get($billingPeriods, 'bi_weekly.0.id'),
            'selectedFilterType' => $request->selected_filter_type ?? 1,
            'providers' => $providers,
        ]);
    }

    public function providerAvailabilityHistory(Request $request, Provider $provider)
    {
        $billingPeriods = $provider->billingPeriodType
            ->periods()
            ->whereDate('billing_periods.start_date', '<=', Carbon::today()->toDateString())
            ->orderBy('billing_periods.start_date', 'desc')
            ->paginate($request->limit ?? 10);

        $billingPeriodsDataset = collect();
        foreach ($billingPeriods as &$billingPeriod) {
            $totalAvailability = $this->providerAvailabilityRepository->getTotalAvailabilityForPeriod($provider, $billingPeriod->start_date, $billingPeriod->end_date);
            $totalAvailabilityHours = $this->providerAvailabilityRepository->getTotalAvailabilityHours($totalAvailability);
            
            $totalAvailabilityHoursSum = $totalAvailabilityHours['activeAppointmentsLength'] + $totalAvailabilityHours['completedAppointmentsLength']
                + $totalAvailabilityHours['visitCreatedAppointmentsLength'] + $totalAvailabilityHours['canceledAppointmentsLength']
                + $totalAvailabilityHours['forApptsRemainingAvailabilityLength'];
            $totalWorkHoursSum = $totalAvailabilityHours['completedAppointmentsLength'] + $totalAvailabilityHours['visitCreatedAppointmentsLength'];

            $billingPeriod->total_availability = $totalAvailability;
            $billingPeriod->total_availability_hours = $totalAvailabilityHoursSum;
            $billingPeriod->total_work_hours = $totalWorkHoursSum;
            $billingPeriod->minimum_work_hours = $this->providerAvailabilityRepository->getMinimumWorkHoursForPeriod($provider, $billingPeriod->start_date, $billingPeriod->end_date);

            $billingPeriodsDataset->push($billingPeriod);
        }

        return response()->json([
            'meta' => ['pagination' => array_except($billingPeriods->toArray(), ['data'])],
            'data' => $billingPeriodsDataset
        ]);
    }
}

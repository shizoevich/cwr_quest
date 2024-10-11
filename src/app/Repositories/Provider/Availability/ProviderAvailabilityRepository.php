<?php

namespace App\Repositories\Provider\Availability;

use App\Repositories\Provider\Availability\ProviderAvailabilityRepositoryInterface;
use App\Components\Availability\ProviderWorkHours;
use App\Helpers\AvailabilityHelper;
use App\Models\Billing\BillingPeriod;
use App\Provider;
use App\Availability;
use App\Appointment;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ProviderAvailabilityRepository implements ProviderAvailabilityRepositoryInterface
{
    public function getDatesFromRequest(array $requestData)
    {
        $billingPeriod = null;
        switch (data_get($requestData, 'selected_filter_type')) {
            case 1:
                //filter by week
                $week = data_get($requestData, 'week');
                $startDate = AvailabilityHelper::getWeekStartDate($week);
                if (is_null($startDate)) {
                    $startDate = Carbon::now()->startOfWeek();
                }
                $endDate = $startDate->copy()->endOfWeek();
                break;
            case 2:
            case 3:
                //filter by billing period
                $billingPeriod = BillingPeriod::findOrFail(data_get($requestData, 'billing_period_id'));
                $startDate = Carbon::parse($billingPeriod->start_date)->startOfDay();
                $endDate = Carbon::parse($billingPeriod->end_date)->endOfDay();
                break;
            default:
                $startDate = Carbon::now()->startOfWeek();
                $endDate = $startDate->copy()->endOfWeek();
        }
        
        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'billing_period' => $billingPeriod,
        ];
    }

    public function getProvidersWithTotalAvailability(array $requestData)
    {
        $dates = $this->getDatesFromRequest($requestData);
        $weeksCount = AvailabilityHelper::getWeeksCount($dates['start_date'], $dates['end_date']);

        $workHoursHelper = new ProviderWorkHours($dates['start_date'], $dates['end_date'], true, true, true);
        $totalAvailabilityMapping = $workHoursHelper->getTotalMapping();

        $billingPeriod = $dates['billing_period'] ?? null;
        $billingPeriodType = isset($billingPeriod) ? $billingPeriod->type : null;

        $providers = Provider::query()
            ->when(isset($billingPeriodType), function(Builder $query) use (&$billingPeriodType) {
                $query->where('billing_period_type_id', '=', $billingPeriodType->id);
            })
            ->whereNotNull('work_hours_per_week')
            ->orderBy('provider_name')
            ->get();

        return $providers->map(function ($provider) use (&$weeksCount, &$totalAvailabilityMapping) {
            $provider->minimum_work_hours = $provider->work_hours_per_week * $weeksCount;
            $provider->total_availability = $totalAvailabilityMapping[$provider->id] ?? [];

            return $provider;
        }, []);
    }

    public function getTotalAvailabilityForPeriod(Provider $provider, $startDate, $endDate, $withUniqueTime=false)
    {
        if (gettype($startDate) === 'string') {
            $startDate = Carbon::parse($startDate);
        }
        if (gettype($endDate) === 'string') {
            $endDate = Carbon::parse($endDate);
        }

        $workHoursHelper = new ProviderWorkHours($startDate, $endDate, true, true, false, $provider->id, $withUniqueTime);
        $totalAvailabilityMapping = $workHoursHelper->getTotalMapping();

        return $totalAvailabilityMapping[$provider->id] ?? [];
    }

    public function getMinimumWorkHoursForPeriod(Provider $provider, $startDate, $endDate)
    {
        if (!isset($provider->work_hours_per_week)) {
            return null;
        }

        if (gettype($startDate) === 'string') {
            $startDate = Carbon::parse($startDate);
        }
        if (gettype($endDate) === 'string') {
            $endDate = Carbon::parse($endDate);
        }

        $weeksCount = AvailabilityHelper::getWeeksCount($startDate, $endDate);

        return $provider->work_hours_per_week * $weeksCount;
    }

    public function getTotalAvailabilityHours(array $totalAvailability)
    {
        return [
            'appointmentsLength' => isset($totalAvailability['appointmentsLength']) ? ($totalAvailability['appointmentsLength'] / 60) : 0,
            'activeAppointmentsLength' => isset($totalAvailability['activeAppointmentsLength']) ? ($totalAvailability['activeAppointmentsLength'] / 60) : 0,
            'completedAppointmentsLength' => isset($totalAvailability['completedAppointmentsLength']) ? ($totalAvailability['completedAppointmentsLength'] / 60) : 0,
            'visitCreatedAppointmentsLength' => isset($totalAvailability['visitCreatedAppointmentsLength']) ? ($totalAvailability['visitCreatedAppointmentsLength'] / 60) : 0,
            'canceledAppointmentsLength' => isset($totalAvailability['canceledAppointmentsLength']) ? ($totalAvailability['canceledAppointmentsLength'] / 60) : 0,
            'rescheduledAppointmentsLength' => isset($totalAvailability['rescheduledAppointmentsLength']) ? ($totalAvailability['rescheduledAppointmentsLength'] / 60) : 0,
            'initialAvailabilityLength' => isset($totalAvailability['initialAvailabilityLength']) ? ($totalAvailability['initialAvailabilityLength'] / 60) : 0,
            'remainingAvailabilityLength' => isset($totalAvailability['remainingAvailabilityLength']) ? ($totalAvailability['remainingAvailabilityLength'] / 60) : 0,
            'forApptsInitialAvailabilityLength' => isset($totalAvailability['forApptsInitialAvailabilityLength']) ? ($totalAvailability['forApptsInitialAvailabilityLength'] / 60) : 0,
            'forApptsRemainingAvailabilityLength' => isset($totalAvailability['forApptsRemainingAvailabilityLength']) ? ($totalAvailability['forApptsRemainingAvailabilityLength'] / 60) : 0,
            'reschedulingInitialAvailabilityLength' => isset($totalAvailability['reschedulingInitialAvailabilityLength']) ? ($totalAvailability['reschedulingInitialAvailabilityLength'] / 60) : 0,
            'reschedulingRemainingAvailabilityLength' => isset($totalAvailability['reschedulingRemainingAvailabilityLength']) ? ($totalAvailability['reschedulingRemainingAvailabilityLength'] / 60) : 0
        ];
    }

    public function updateRemainingLength(Availability $availability)
    {
        $startDate = Carbon::parse($availability->start_date);
        $availabilityStart = $startDate->copy()->setTimeFromTimeString($availability->start_time);
        $availabilityEnd = $availabilityStart->copy()->addMinutes($availability->length ?? 0);
        $availabilityRemainingLength = $availability->length ?? 0;
        $appointments = $this->getAppointmentsByAvailability($availability);

        $usedTime = [];
        foreach ($appointments as $appointment) {
            if (in_array($appointment->time, $usedTime)) {
                continue;
            }

            $appointmentDate = Carbon::createFromTimestamp($appointment->time);
            $appointmentStart = $appointmentDate;
            $appointmentEnd = $appointmentStart->copy()->addMinutes($appointment->visit_length ?? 0);

            if (
                ($appointmentStart->gte($availabilityStart) && $appointmentStart->lt($availabilityEnd)) || 
                ($appointmentEnd->gt($availabilityStart) && $appointmentEnd->lte($availabilityEnd))
            ) {
                $tempStart = $appointmentStart->lt($availabilityStart) ? $availabilityStart->copy() : $appointmentStart;
                $tempEnd = $appointmentEnd->gt($availabilityEnd) ? $availabilityEnd->copy() : $appointmentEnd;
                $availabilityRemainingLength -= $tempEnd->diffInMinutes($tempStart);
                array_push($usedTime, $appointment->time);
            }
        }

        // logic to prevent 'updated' event dispatch 
        $dispatcher = Availability::getEventDispatcher();
        Availability::unsetEventDispatcher();

        $availability->update(['remaining_length' => $availabilityRemainingLength > 0 ? $availabilityRemainingLength : 0]);

        Availability::setEventDispatcher($dispatcher);   
    }

    private function getAppointmentsByAvailability(Availability $availability)
    {
        return Appointment::query()
            ->select(['*', DB::raw('DATE(FROM_UNIXTIME(`appointments`.`time`)) AS appt_date')])
            ->whereNotNull('patients_id')
            ->where('providers_id', '=', $availability->provider_id)
            ->havingRaw("appt_date = DATE('{$availability->start_date}')")
            ->get();
    }
}

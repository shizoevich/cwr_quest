<?php

namespace App\Traits\Providers;

use Illuminate\Support\Collection;
use App\PatientVisit;
use App\Appointment;
use App\Models\Provider\Salary;
use App\Status;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use DateTime;

trait ProviderSummaryTrait
{
    /**
    * @param Collection<\App\Provider> $providers
    */
    private function getTotalWorkedYearsMapping(Collection $providers)
    {
        return $providers->reduce(function ($carry, $item) {
            $salaryTimesheetDate = PatientVisit::query()->select(['date', 'provider_id'])
                ->where('provider_id', $item->id)
                ->where('deleted_at', NULL)
                ->orderBy('date')
                ->first();

            if (isset($salaryTimesheetDate)) {
                $startDateOfWork = strtotime($salaryTimesheetDate['date']);
                $dt1 = new DateTime(date('Y-m-d H:i:s', $startDateOfWork));
                $dt2 = new DateTime();
                $diff = date_diff($dt1, $dt2);

                $carry[$salaryTimesheetDate['provider_id']] = [
                    'provider_id' => $salaryTimesheetDate['provider_id'],
                    'date' => $salaryTimesheetDate['date'],
                    'totalWorkedYears' => $diff->format("Year: %y, Month: %m, Days: %d."),
                ];
            }
            
            return $carry;
        }, []);
    }

    private function getAppointmentsPerYearCountMapping(Carbon $startDate, Carbon $endDate)
    {
        $visitCreatedStatusId = Status::getVisitCreatedId();
        $visitCreatedAppointments = Appointment::query()
            ->select(['appointments.providers_id', DB::raw('DATE(FROM_UNIXTIME(`appointments`.`time`)) AS appt_date')])
            ->whereNotNull('appointments.providers_id')
            ->where('appointment_statuses_id', '=', $visitCreatedStatusId)
            ->havingRaw("appt_date >= DATE('{$startDate->toDateString()}')")
            ->havingRaw("appt_date <= DATE('{$endDate->toDateString()}')")
            ->get();

        return $visitCreatedAppointments->reduce(function ($carry, $item) {
            if (isset($carry[$item->providers_id])) {
                $carry[$item->providers_id] += 1;
            } else {
                $carry[$item->providers_id] = 1;
            }

            return $carry;
        }, []);
    }

    private function getSickTimeMapping(Carbon $startDate, Carbon $endDate)
    {
        $sickTimeData = Salary::query()
            ->selectRaw('
                `salary`.`provider_id`,
                SUM(salary.paid_fee / 100) as seek_time_paid,
                SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(salary.additional_data, "$.visit_count")) AS UNSIGNED)) as total_visit_count
            ')
            ->join('billing_periods', 'billing_periods.id', '=', 'salary.billing_period_id')
            ->whereDate('billing_periods.start_date', '>=', $startDate->toDateString())
            ->whereDate('billing_periods.start_date', '<', $endDate->toDateString())
            ->where('salary.type', '=', Salary::TYPE_SICK_TIME)
            ->groupBy('salary.provider_id')
            ->get();

        return $sickTimeData->reduce(function ($carry, $item) {
            $carry[$item->provider_id] = [
                'seek_time_paid' => $item->seek_time_paid,
                'total_visit_count' => $item->total_visit_count,
            ];
            return $carry;
        }, []);
    }
}

<?php

namespace App\Observers;

use App\Models\Provider\Salary;
use App\Models\Provider\SalaryTimesheetLateCancellation;

class SalaryTimesheetLateCancellationObserver
{
    public function created(SalaryTimesheetLateCancellation $timesheetLateCancellation)
    {
        if(!$timesheetLateCancellation->is_custom_created) {
            $fee = $timesheetLateCancellation->amount * Salary::LATE_CANCELLATION_PROVIDER_PERCENTAGE;
            Salary::query()
                ->create([
                    'provider_id' => $timesheetLateCancellation->provider_id,
                    'type' => Salary::TYPE_CREATED_FROM_TIMESHEET_LATE_CANCELLATION,
                    'fee' => $fee,
                    'paid_fee' => $fee,
                    'billing_period_id' => $timesheetLateCancellation->billing_period_id,
                    'date' => $timesheetLateCancellation->date,
                    'additional_data' => [
                        'salary_timesheet_late_cancellation_id' => $timesheetLateCancellation->getKey(),
                    ]
                ]);
        }
    }
}
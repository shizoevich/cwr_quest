<?php

namespace App\Repositories\Provider\Salary;

use App\Models\Billing\BillingPeriodType;
use Carbon\Carbon;

class BillingPeriodRepository implements BillingPeriodRepositoryInterface
{
    public function all(): array
    {
        $billingPeriods = [];
        BillingPeriodType::query()->each(function(BillingPeriodType $type) use (&$billingPeriods) {
            $billingPeriods[$type->name] = $type->periods()->where('start_date', '<=', Carbon::today())->orderByDesc('start_date')->limit(100)->get()->toArray();
        });
        
        return $billingPeriods;
    }
}
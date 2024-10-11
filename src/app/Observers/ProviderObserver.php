<?php

namespace App\Observers;

use App\Models\Billing\BillingPeriodType;
use App\Provider;

class ProviderObserver
{
    public function creating(Provider $provider)
    {
        $provider->billing_period_type_id = optional(BillingPeriodType::getBiWeekly())->id;
    }
}
<?php

namespace App\Models\Provider;

use Illuminate\Database\Eloquent\Model;

class SalaryTimesheetNotification extends Model
{
    protected $fillable = [
        'billing_period_id',
        'provider_id',
        'viewed_at',
        'remind_after',
    ];
}

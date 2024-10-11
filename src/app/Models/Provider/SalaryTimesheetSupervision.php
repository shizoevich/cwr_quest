<?php

namespace App\Models\Provider;

use Illuminate\Database\Eloquent\Model;

class SalaryTimesheetSupervision extends Model
{
    protected $fillable = [
        'billing_period_id',
        'provider_id',
        'supervisor_id',
        'supervision_hours',
        'comment'
    ];
}

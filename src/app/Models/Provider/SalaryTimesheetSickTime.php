<?php

namespace App\Models\Provider;

use App\Appointment;
use App\Models\Billing\BillingPeriod;
use App\Provider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SalaryTimesheetSickTime extends Model
{
    protected $fillable = [
        'billing_period_id',
        'provider_id',
        'date',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id', 'id');
    }

    public function billingPeriod(): BelongsTo
    {
        return $this->belongsTo(BillingPeriod::class, 'billing_period_id', 'id');
    }

    public function appointments(): BelongsToMany
    {
        return $this->belongsToMany(Appointment::class, 'salary_timesheet_sick_times_appointments', 'sick_time_id', 'appointment_id');
    }
}

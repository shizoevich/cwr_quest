<?php

namespace App\Models\Provider;

use App\Models\Billing\BillingPeriod;
use App\Provider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Provider\SalaryTimesheet
 *
 * @property int $id
 * @property int $billing_period_id
 * @property int $provider_id
 * @property float $seek_time
 * @property bool $monthly_meeting_attended
 * @property bool $changed_appointment_statuses
 * @property bool $completed_ia_and_pn
 * @property bool $set_diagnoses
 * @property bool $completed_timesheet
 * @property string|null $complaint
 * @property int|null $is_resolve_complaint
 * @property \Illuminate\Support\Carbon|null $signed_at
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read BillingPeriod $billingPeriod
 * @property-read Provider $provider
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTimesheet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTimesheet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTimesheet query()
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTimesheet whereBillingPeriodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTimesheet whereChangedAppointmentStatuses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTimesheet whereComplaint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTimesheet whereCompletedIaAndPn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTimesheet whereCompletedTimesheet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTimesheet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTimesheet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTimesheet whereIsResolveComplaint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTimesheet whereMonthlyMeetingAttended($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTimesheet whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTimesheet whereReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTimesheet whereSeekTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTimesheet whereSetDiagnoses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTimesheet whereSignedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTimesheet whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SalaryTimesheet extends Model
{
    protected $fillable = [
        'billing_period_id',
        'provider_id',
        'seek_time',
        'monthly_meeting_attended',
        'changed_appointment_statuses',
        'completed_ia_and_pn',
        'set_diagnoses',
        'completed_timesheet',
        'reviewed_at',
        'complaint',
        'is_resolve_complaint',
        'signed_at',
    ];

    protected $casts = [
        'billing_period_id'            => 'int',
        'provider_id'                  => 'int',
        'seek_time'                    => 'float',
        'monthly_meeting_attended'     => 'bool',
        'changed_appointment_statuses' => 'bool',
        'completed_ia_and_pn'          => 'bool',
        'set_diagnoses'                => 'bool',
        'completed_timesheet'          => 'bool',
    ];

    protected $dates = [
        'reviewed_at',
        'signed_at',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id', 'id');
    }

    public function billingPeriod(): BelongsTo
    {
        return $this->belongsTo(BillingPeriod::class, 'billing_period_id', 'id');
    }
}

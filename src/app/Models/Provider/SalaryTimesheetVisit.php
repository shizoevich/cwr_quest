<?php

namespace App\Models\Provider;

use App\Patient;
use App\PatientVisit;
use App\Provider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Provider\SalaryTimesheetVisit
 *
 * @property int $id
 * @property int $visit_id
 * @property int $billing_period_id
 * @property int $patient_id
 * @property int $provider_id
 * @property string $date
 * @property bool $is_overtime
 * @property bool $is_telehealth
 * @property bool $is_custom_created
 * @property \Carbon\Carbon|null $provider_reviewed_at
 * @property \Carbon\Carbon|null $accepted_at
 * @property \Carbon\Carbon|null $declined_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Patient $patient
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Provider\SalaryTimesheetVisit onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\SalaryTimesheetVisit whereAcceptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\SalaryTimesheetVisit whereBillingPeriodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\SalaryTimesheetVisit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\SalaryTimesheetVisit whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\SalaryTimesheetVisit whereDeclinedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\SalaryTimesheetVisit whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\SalaryTimesheetVisit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\SalaryTimesheetVisit whereIsCustomCreated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\SalaryTimesheetVisit whereIsOvertime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\SalaryTimesheetVisit wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\SalaryTimesheetVisit whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\SalaryTimesheetVisit whereProviderReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\SalaryTimesheetVisit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Provider\SalaryTimesheetVisit whereVisitId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Provider\SalaryTimesheetVisit withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Provider\SalaryTimesheetVisit withoutTrashed()
 * @mixin \Eloquent
 */
class SalaryTimesheetVisit extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'visit_id',
        'billing_period_id',
        'provider_id',
        'patient_id',
        'date',
        'is_overtime',
        'is_telehealth',
        'is_custom_created',
        'provider_reviewed_at',
        'accepted_at',
        'declined_at',
        'deleted_at',
    ];
    
    protected $casts = [
        'visit_id' => 'int',
        'billing_period_id' => 'int',
        'patient_id' => 'int',
        'provider_id' => 'int',
        'is_overtime' => 'bool',
        'is_telehealth' => 'bool',
        'is_custom_created' => 'bool',
    ];
    
    protected $dates = [
        'provider_reviewed_at',
        'accepted_at',
        'declined_at',
    ];
    
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
    
    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }
    
    public function visit()
    {
        return $this->belongsTo(PatientVisit::class, 'visit_id');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PatientInsurancePlan
 *
 * @property int $id
 * @property int $insurance_id
 * @property string $name
 * @property int|null $parent_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientInsurancePlan[] $childPlans
 * @property-read \App\PatientInsurance $insurances
 * @property-read \App\PatientInsurancePlan $parentPlan
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientInsurancePlanProcedure[] $proceduresPrices
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsurancePlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsurancePlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsurancePlan whereInsuranceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsurancePlan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsurancePlan whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsurancePlan whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PatientInsurancePlan extends Model
{
    const EXPIRATION_DOESNT_EXPIRE_ID = 1;
    const EXPIRATION_EXPIRING_SOON_ID = 2;
    const EXPIRATION_EXPIRED_ID = 3;

    protected $table = 'patient_insurances_plans';

    protected $fillable = [
        'insurance_id',
        'name',
        'procedures_prices',
        'parent_id',
        'is_verification_required',
        'requires_reauthorization_document',
        'reauthorization_notification_visits_count',
        'reauthorization_notification_days_count',
        'need_collect_copay_for_telehealth',
    ];
    
    protected $casts = [
        'need_collect_copay_for_telehealth' => 'bool',
    ];

    static function getExpirationsList()
    {
        return [
            ['id' => self::EXPIRATION_DOESNT_EXPIRE_ID, 'title' => "Doesn't Expire"],
            ['id' => self::EXPIRATION_EXPIRING_SOON_ID, 'title' => 'Expiring Soon'],
            ['id' => self::EXPIRATION_EXPIRED_ID, 'title' => 'Expired']
        ];
    }

    public function insurances()
    {
        return $this->belongsTo(PatientInsurance::class, 'insurance_id', 'id');
    }

    public function proceduresPrices()
    {
        return $this->hasMany( PatientInsurancePlanProcedure::class, 'plan_id','id');
    }

    public function childPlans()
    {
        return $this->hasMany( PatientInsurancePlan::class, 'parent_id','id');
    }

    public function parentPlan()
    {
        return $this->hasOne( PatientInsurancePlan::class, 'id','parent_id');
    }
}

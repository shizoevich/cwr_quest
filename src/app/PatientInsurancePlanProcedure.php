<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PatientInsurancePlanProcedure
 *
 * @property int $id
 * @property int|null $tariff_plan_id
 * @property int $plan_id
 * @property int $procedure_id
 * @property mixed $price
 * @property mixed $telehealth_price
 * @property int $type
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsurancePlanProcedure whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsurancePlanProcedure whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsurancePlanProcedure wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsurancePlanProcedure wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsurancePlanProcedure whereProcedureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsurancePlanProcedure whereTariffPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsurancePlanProcedure whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsurancePlanProcedure whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PatientInsurancePlanProcedure extends Model
{
    const TYPE_MASTER = 1;
    const TYPE_DEFAULT = 2;

    protected $table = 'patient_insurances_plans_procedures';

    protected $fillable = [
        'plan_id',
        'procedure_id',
        'tariff_plan_id',
        'price',
        'telehealth_price',
        'type'
    ];

    protected $casts = [
        'price' => 'float(8,2)',
        'type' =>'integer',
        'plane_id' =>'integer',
        'procedure_id' =>'integer',
    ];
    
    public function getPriceAttribute($value)
    {
        return $value ? floatval($value / 100) : $value;
    }
    
    public function setPriceAttribute($value)
    {
        if($value) {
            $this->attributes['price'] = $value * 100;
        } else {
            $this->attributes['price'] = $value;
        }
    }
    
    public function getTelehealthPriceAttribute($value)
    {
        return $value ? floatval($value / 100) : $value;
    }
    
    public function setTelehealthPriceAttribute($value)
    {
        if($value) {
            $this->attributes['telehealth_price'] = $value * 100;
        } else {
            $this->attributes['telehealth_price'] = $value;
        }
    }
}

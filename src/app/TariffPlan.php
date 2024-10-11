<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\TariffPlan
 *
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientInsurancePlanProcedure[] $prices
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Provider[] $providers
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TariffPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TariffPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TariffPlan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TariffPlan whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TariffPlan extends Model
{
    protected $table = 'tariffs_plans';

    protected $fillable = [
        'name',
        'fee_per_missing_pn',
    ];

    public function providers()
    {
        return $this->belongsToMany('App\Provider', 'providers_tariffs_plans', 'tariff_plan_id', 'provider_id');
    }

    public function prices()
    {
        return $this->hasMany('App\PatientInsurancePlanProcedure', 'tariff_plan_id', 'id');
    }
    
    public function getFeePerMissingPnAttribute($value)
    {
        return $value ? floatval($value / 100) : $value;
    }
    
    public function setFeePerMissingPnAttribute($value)
    {
        if($value) {
            $this->attributes['fee_per_missing_pn'] = $value * 100;
        } else {
            $this->attributes['fee_per_missing_pn'] = $value;
        }
    }
}

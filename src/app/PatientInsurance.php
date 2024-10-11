<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * App\PatientInsurance
 *
 * @property int $id
 * @property string $insurance
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientInsurancePlan[] $plans
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Provider[] $providers
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsurance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsurance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsurance whereInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsurance whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $external_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsurance whereExternalId($value)
 */
class PatientInsurance extends Model {
    protected $table = 'patient_insurances';

    protected $fillable = [
        'external_id',
        'insurance',
        'address_line_1',
        'city',
        'state',
        'zip',
    ];

    public function providers()
    {
        return $this->belongsToMany(Provider::class, 'provider_insurances', 'insurance_id', 'provider_id');
    }

    public function plans()
    {
        return $this->hasMany(PatientInsurancePlan::class, 'insurance_id')
            ->whereNull('parent_id');
    }

    public static function getCashId(): int
    {
        return Cache::rememberForever('patient_insurance:cash', function() {
            return static::where('insurance', 'Cash')->first()['id'];
        });
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PatientInsuranceProcedure
 *
 * @property int $id
 * @property string|null $code
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsuranceProcedure whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsuranceProcedure whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsuranceProcedure whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsuranceProcedure whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsuranceProcedure whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $pos
 * @property string|null $modifier_a
 * @property string|null $modifier_b
 * @property string|null $modifier_c
 * @property string|null $modifier_d
 * @property float|null $charge
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsuranceProcedure whereCharge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsuranceProcedure whereModifierA($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsuranceProcedure whereModifierB($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsuranceProcedure whereModifierC($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsuranceProcedure whereModifierD($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInsuranceProcedure wherePos($value)
 */
class PatientInsuranceProcedure extends Model
{
    public const EAP_CPT_CODE = '99404';

    public const CASH_CPT_CODE = '00001';

    protected $table = 'patient_insurances_procedures';

    protected $fillable = [
        'code',
        'name',
        'pos',
        'modifier_a',
        'modifier_b',
        'modifier_c',
        'modifier_d',
        'charge',
    ];

    public static function getInitialProcedure()
    {
        return \Cache::rememberForever('procedures:initial', function () {
            return static::where('code', 90791)->first();
        });
    }

    public static function get60MinProcedure()
    {
        return \Cache::rememberForever('procedures:60_min', function () {
            return static::where('code', 90837)->first();
        });
    }

    public static function getInitialProcedureId()
    {
        $procedure = self::getInitialProcedure();
        return isset($procedure) ? $procedure['id'] : null;
    }

    public static function get60MinProcedureId()
    {
        $procedure = self::get60MinProcedure();
        return isset($procedure) ? $procedure['id'] : null;
    }
}

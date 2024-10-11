<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PatientAlert
 *
 * @property int $id
 * @property int $patient_id
 * @property int $officeally_alert_id
 * @property string $date_created
 * @property string $message
 * @property string|null $resolved_by
 * @property string|null $date_resolved
 * @property string $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Patient $patient
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAlert whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAlert whereDateCreated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAlert whereDateResolved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAlert whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAlert whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAlert whereOfficeallyAlertId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAlert wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAlert whereResolvedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAlert whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAlert whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PatientAlert extends Model
{
    protected $table = 'patient_alerts';

    protected $guarded = [];

    public function patient()
    {
        return $this->belongsTo('App\Patient');
    }

    /**
     * @param $value
     *
     * @return float|int
     */
    public function getCoPayAttribute($value)
    {
        return money_round($value / 100);
    }

     /**
     * @param $value
     *
     * @return void
     */
    public function setCoPayAttribute($value)
    {
        $this->attributes['co_pay'] = intval(floatval($value) * 100);
    }

    /**
     * @param $value
     *
     * @return float|int
     */
    public function getDeductibleAttribute($value)
    {
        return money_round($value / 100);
    }

    /**
     * @param $value
     *
     * @return void
     */
    public function setDeductibleAttribute($value)
    {
        $this->attributes['deductible'] = intval(floatval($value) * 100);
    }

    /**
     * @param $value
     *
     * @return float|int
     */
    public function getDeductibleMetAttribute($value)
    {
        return money_round($value / 100);
    }

    /**
     * @param $value
     *
     * @return void
     */
    public function setDeductibleMetAttribute($value)
    {
        $this->attributes['deductible_met'] = intval(floatval($value) * 100);
    }

    /**
     * @param $value
     *
     * @return float|int
     */
    public function getDeductibleRemainingAttribute($value)
    {
        return money_round($value / 100);
    }

    /**
     * @param $value
     *
     * @return void
     */
    public function setDeductibleRemainingAttribute($value)
    {
        $this->attributes['deductible_remaining'] = intval(floatval($value) * 100);
    }

     /**
     * @param $value
     *
     * @return float|int
     */
    public function getInsurancePayAttribute($value)
    {
        return money_round($value / 100);
    }

    /**
     * @param $value
     *
     * @return void
     */
    public function setInsurancePayAttribute($value)
    {
        $this->attributes['insurance_pay'] = intval(floatval($value) * 100);
    }
}

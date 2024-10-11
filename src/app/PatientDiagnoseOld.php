<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PatientDiagnose
 *
 * @todo delete this model
 *
 * @property int $patient_officeally_id
 * @property string $diagnose
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Patient $patient
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDiagnoseOld whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDiagnoseOld whereDiagnose($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDiagnoseOld wherePatientOfficeallyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDiagnoseOld whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PatientDiagnoseOld extends Model {
    protected $table = "patient_diagnoses_old";

    protected $guarded = [];

    protected $primaryKey = 'patient_officeally_id';

    public $incrementing = false;

    public function patient() {
        return $this->belongsTo('App\Patient', 'patient_officeally_id', 'patient_id');
    }
}

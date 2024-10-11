<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PatientFormFirst
 *
 * @property-read \App\Patient $patient
 * @mixin \Eloquent
 */
class PatientFormFirst extends Model
{
	protected $table = 'patient_form_firsts';

	protected $guarded = [];

	public function patient(){

		return $this->belongsTo('App\Patient', 'patients_id', 'id');
	}
}

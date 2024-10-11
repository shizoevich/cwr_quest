<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PatientVisitBilling
 *
 * @property int $id
 * @property int $visit_id
 * @property int $pos
 * @property int $cpt
 * @property int $insurance_procedure_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \App\PatientInsuranceProcedure $insurance_procedure
 * @property-read \App\Patient $patient
 * @property-read \App\PatientVisit $visit
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\PatientVisitBilling onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientVisitBilling whereCpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientVisitBilling whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientVisitBilling whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientVisitBilling whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientVisitBilling whereInsuranceProcedureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientVisitBilling wherePos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientVisitBilling whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientVisitBilling whereVisitId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\PatientVisitBilling withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\PatientVisitBilling withoutTrashed()
 * @mixin \Eloquent
 */
class PatientVisitBilling extends Model
{

    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'patient_visit_billings';

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'visit_id',
        'pos',
        'cpt',
        'insurance_procedure_id'
    ];

    public function patient()
    {
        return $this->hasOne('\App\Patient','patient_id');
    }

   public function visit()
   {
       return $this->hasOne('\App\PatientVisit','visit_id');
   }

   public function insurance_procedure()
   {
       return $this->hasOne('\App\PatientInsuranceProcedure','insurance_procedure_id');
   }
}

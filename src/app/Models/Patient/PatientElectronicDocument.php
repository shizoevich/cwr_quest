<?php

namespace App\Models\Patient;

use App\Appointment;
use App\AssessmentForm;

use App\Models\Diagnose;
use App\Models\SubmittedReauthorizationRequestForm;
use App\Patient;
use App\PatientDocumentShared;
use App\PatientVisit;
use App\Provider;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Patient\PatientElectronicDocument
 *
 * @property int $id
 * @property int $document_type_id
 * @property int $provider_id
 * @property int $patient_id
 * @property string $document_data
 * @property string $start_editing_date
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Patient $patient
 * @property-read \App\AssessmentForm $type
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Patient\PatientElectronicDocument onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientElectronicDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientElectronicDocument whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientElectronicDocument whereDocumentData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientElectronicDocument whereDocumentTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientElectronicDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientElectronicDocument wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientElectronicDocument whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientElectronicDocument whereStartEditingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientElectronicDocument whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Patient\PatientElectronicDocument withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Patient\PatientElectronicDocument withoutTrashed()
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientElectronicDocument discharge()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientElectronicDocument initialAssessment()
 */
class PatientElectronicDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'document_type_id',
        'provider_id',
        'patient_id',
        'document_data',
        'start_editing_date',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient(){
        return $this->belongsTo(Patient::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function provider(){
        return $this->belongsTo(Provider::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(AssessmentForm::class, 'document_type_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documentShared()
    {
        return $this->hasMany(PatientDocumentShared::class, 'patient_documents_id', 'id')
            ->where('document_model', static::class);
    }

    public function submittedReauthorizationRequestForm(): MorphOne
    {
        return $this->morphOne(SubmittedReauthorizationRequestForm::class, 'document');
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeDischarge($query) {
        $ids = AssessmentForm::getFileTypeIDsLikeDischarge();

        return $query->whereIn('document_type_id', $ids);
    }

    public function scopeInitialAssessment($query) {
        $ids = AssessmentForm::getFileTypeIDsLikeInitialAssessment();

        return $query->whereIn('document_type_id', $ids);
    }
    
    /**
     * @param Carbon|null $customCreatedAt
     */
    public function attachToAppointment($customCreatedAt = null)
    {
        $data = json_decode($this->document_data, true);
        $dos = data_get($data, 'date_of_service');
        if(!$dos) {
            return;
        }
        $dos = Carbon::parse($dos)->startOfDay();
        $appointmentIds = Appointment::query()
            ->statusNotCancel()
            ->where('providers_id', $this->provider_id)
            ->where('patients_id', $this->patient_id)
            ->where('time', '>=', $dos->timestamp)
            ->where('time', '<=', $dos->copy()->endOfDay()->timestamp)
            ->pluck('id');
        if($appointmentIds->isNotEmpty()) {
            PatientVisit::query()
                ->whereIn('appointment_id', $appointmentIds)
                ->withTrashed()
                ->each(function ($patientVisit) {
                    $patientVisit->update(['needs_update_salary' => 1]);
                });
            $className = get_class($this);
            $key = $this->getKey();
            Appointment::query()
                ->whereIn('id', $appointmentIds)
                ->each(function ($appointment) use ($className, $key, $customCreatedAt) {
                    $appointment->update([
                        'initial_assessment_type' => $className,
                        'initial_assessment_id' => $key,
                        'initial_assessment_created_at' => $customCreatedAt ? $customCreatedAt->toDateTimeString() : $this->created_at,
                    ]);
                });
        }
    }
    
    public function detachFromAppointment()
    {
        $appointmentIds = Appointment::query()
            ->where('initial_assessment_type', get_class($this))
            ->where('initial_assessment_id', $this->getKey())
            ->pluck('id');
        if($appointmentIds->isNotEmpty()) {
            PatientVisit::query()
                ->whereIn('appointment_id', $appointmentIds)
                ->withTrashed()
                ->each(function ($patientVisit) {
                    $patientVisit->update(['needs_update_salary' => 1]);
                });
            Appointment::query()
                ->whereIn('id', $appointmentIds)
                ->each(function ($appointment){
                    $appointment->update([
                        'initial_assessment_type' => null,
                        'initial_assessment_id' => null,
                        'initial_assessment_created_at' => null,
                    ]);
                });
        }
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function diagnoses()
    {
        return $this->belongsToMany(Diagnose::class, 'patient_electronic_document_diagnoses');
    }
}

<?php

namespace App;

use App\Models\Patient\DocumentRequest\PatientDocumentRequestItem;
use App\Models\SubmittedReauthorizationRequestForm;
use App\Repositories\Appointment\Model\AppointmentRepositoryInterface;
use App\Repositories\Patient\PatientDocumentRepositoryInterface;
use App\Scopes\PatientDocuments\DocumentsForAllScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * App\PatientDocument
 *
 * @property int $id
 * @property int $patient_id
 * @property string $original_document_name
 * @property string $aws_document_name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property int|null $document_type_id
 * @property int|null $document_request_item_id
 * @property string|null $other_document_type
 * @property int $visible
 * @property \Carbon\Carbon|null $deleted_at
 * @property int $only_for_admin
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientDocumentComment[] $comments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientDocumentShared[] $documentShared
 * @property-read \App\PatientDocumentType|null $documentType
 * @property-read \App\Patient $patient
 * @property-read PatientDocumentRequestItem $documentRequestItem
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\PatientDocument onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocument whereAwsDocumentName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocument whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocument whereDocumentTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocument whereOnlyForAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocument whereOriginalDocumentName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocument whereOtherDocumentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocument wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocument whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocument whereVisible($value)
 * @method static \Illuminate\Database\Query\Builder|\App\PatientDocument withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\PatientDocument withoutTrashed()
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocument discharged()
 */
class PatientDocument extends Model {

    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected static function boot() {
        parent::boot();
        static::addGlobalScope(new DocumentsForAllScope());
    }

    protected $table = 'patient_documents';

    protected $guarded = [];

    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    public function patient() {
        return $this->belongsTo('App\Patient');
    }

    public function documentType() {
        return $this->belongsTo('App\PatientDocumentType', 'document_type_id');
    }

    public function comments() {
        return $this->hasMany('App\PatientDocumentComment');
    }

    public function documentShared() {
        return $this->hasMany(PatientDocumentShared::class, 'patient_documents_id');
    }

    public function documentRequestItem()
    {
        return $this->belongsTo(PatientDocumentRequestItem::class, 'document_request_item_id');
    }

    public function uploadInfo()
    {
        return $this->hasOne(PatientDocumentUploadInfo::class, 'patient_document_id');
    }

    public function submittedReauthorizationRequestForm(): MorphOne
    {
        return $this->morphOne(SubmittedReauthorizationRequestForm::class, 'document');
    }

    public static function withoutAdminScope()
    {
        return static::withoutGlobalScope(DocumentsForAllScope::class);
    }

    public function scopeDischarged($query)
    {
        $ids = PatientDocumentType::getFileTypeIDsLikeDischarge();

        return $query->whereIn('document_type_id', $ids);
    }
    
    public function scopeInitialAssessment($query)
    {
        $ids = PatientDocumentType::getFileTypeIDsLikeInitialAssessment();
        
        return $query->whereIn('document_type_id', $ids);
    }
    
    /**
     * @param Appointment $appointment
     * @param null        $customCreatedAt
     */
    public function attachToAppointment(Appointment $appointment, $customCreatedAt = null)
    {
        PatientVisit::query()
            ->where('appointment_id', $appointment->id)
            ->withTrashed()
            ->each(function ($patientVisit) {
                $patientVisit->update(['needs_update_salary' => 1]); 
            });

        $appointment->update([
            'initial_assessment_type'       => PatientDocument::class,
            'initial_assessment_id'         => $this->getKey(),
            'initial_assessment_created_at' => $customCreatedAt ? $customCreatedAt->toDateTimeString() : $this->created_at,
        ]);
    }
    
    /**
     * @param Carbon|null $customCreatedAt
     */
    public function findAppointmentAndAttach($customCreatedAt = null) 
    {
        $appointmentStatuses = [
            Status::getActiveId(),
            Status::getCompletedId(),
            Status::getVisitCreatedId()
        ];
        $dos = Carbon::parse($this->created_at);

        $appointment = $this->patient->appointments()
            ->where('is_initial', 1)
            ->whereNull('initial_assessment_id')
            ->whereIn('appointment_statuses_id', $appointmentStatuses)
            ->whereBetween('time', [
                $dos->copy()->subDays(90)->startOfDay()->timestamp,
                $dos->copy()->endOfDay()->timestamp,
            ])
            ->orderBy('time', 'desc')
            ->first();

        if (isset($appointment)) {
            $this->attachToAppointment($appointment, $customCreatedAt);
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
                ->each(function ($appointment) {
                    $appointment->update([
                        'initial_assessment_type' => null,
                        'initial_assessment_id' => null,
                        'initial_assessment_created_at' => null,
                    ]);
                });

        }
    }

    public static function getPaymentForServiceChangeDate(): Carbon
    {
        return Carbon::parse(config('app.payment_for_service_change_date'))->startOfDay();
    }

    public static function getUphealConsentAddedDate(): Carbon
    {
        return Carbon::parse(config('app.upheal_consent_added_date'))->startOfDay();
    }

    public function getLogData()
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'original_document_name' => $this->original_document_name,
            'aws_document_name' => $this->aws_document_name,
            'is_tridiuum_document' => $this->is_tridiuum_document,
            'google_drive' => $this->google_drive,
            'document_type_id' => $this->document_type_id,
            'document_type_name' => optional($this->documentType)->type,
            'document_request_item_id' => $this->document_request_item_id,
            'other_document_type' => $this->other_document_type,
            'visible' => $this->visible,
            'deleted_at' => $this->deleted_at,
            'only_for_admin' => $this->only_for_admin
        ];
    }

    public function getDirtyWithOriginal()
    {
        $result = [];
        $dirtyFields = $this->getDirty();

        foreach ($dirtyFields as $fieldName => $fieldValue) {
            $result[$fieldName] = [
                'prev' => $this->getOriginal($fieldName),
                'curr' => $fieldValue,
            ];
        }

        return $result;
    }

    public function getCreateLogMessage()
    {
        return 'PatientDocument created: ' . $this->getLogMessageIdentifier();
    }

    public function getUpdateLogMessage($dirtyFields = null)
    {
        if (empty($dirtyFields)) {
            $dirtyFields = $this->getDirtyWithOriginal();
        }

        $messagesList = [];

        foreach ($this->getScalarChangeableFields() as $fieldName => $message) {
            if (isset($dirtyFields[$fieldName])) {
                $messagesList[] = "$message changed from '{$dirtyFields[$fieldName]['prev']}' to '{$dirtyFields[$fieldName]['curr']}'";
            }
        }

        return 'PatientDocument updated: ' . implode('; ', $messagesList);
    }

    public function getDeleteLogMessage()
    {
        return 'PatientDocument deleted: ' . $this->getLogMessageIdentifier();
    }

    public function getRestoreLogMessage()
    {
        return 'PatientDocument restored ' . $this->getLogMessageIdentifier();
    }

    public function getLogMessageIdentifier()
    {
        return "{$this->id}; {$this->patient_id}; {$this->original_document_name}; {$this->aws_document_name};"
            . Carbon::parse($this->created_at)->toDateString();
    }

    public function getScalarChangeableFields()
    {
        return [
            'patient_id' => 'Patient id',
            'original_document_name' => 'Original document name',
            'aws_document_name' => 'Aws document name',
            'is_tridiuum_document' => 'Is tridium document',
            'google_drive' => 'Google drive',
            'document_type_id' => 'Document type id',
            'document_request_item_id' => 'Document request item id',
            'other_document_type' => 'Other document type',
            'visible' =>  'Visible',
            'deleted_at' => 'Deleted at',
            'only_for_admin' => 'Only for admin'
        ];
    }
}

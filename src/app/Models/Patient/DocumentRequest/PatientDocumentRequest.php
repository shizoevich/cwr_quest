<?php

namespace App\Models\Patient\DocumentRequest;

use App\Patient;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientDocumentRequest extends Model
{
    const NEW_PATIENT = 'new_patient';
    const AGREEMENT_FOR_SERVICE = 'agreement_for_service_and_hipaa_privacy_notice_and_patient_rights';
    const ATTENDANCE_POLICY = 'attendance_policy';

    protected $fillable = [
        'patient_id',
        'sent_by',
        'hash',
        'expiring_at',
        'sent_to_email',
        'sent_to_phone',
        'sent_to_phone_error',
        'retrieve_count',
        'last_retrieved_at',
        'mandrill_event_id',
        'sent_at',
        'delivered_at',
        'opened_at',
        'deferral_at',
        'hard_bounced_at',
        'soft_bounced_at',
        'bounced_at',
        'click_at',
        'spam_at',
        'unsub_at',
        'rejected_at'
    ];

    protected $casts = [
        'patient_id' => 'int',
        'sent_by' => 'int',
    ];

    protected $dates = [
        'expiring_at',
        'last_retrieved_at'
    ];

    /**
     * @return BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * @return BelongsTo
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by', 'id');
    }

    /**
     * @return HasMany
     */
    public function items()
    {
        return $this->hasMany(PatientDocumentRequestItem::class, 'request_id', 'id');
    }

    public function getRouteKeyName()
    {
        return 'hash';
    }

    /**
     * @return HasMany
     */
    public function sharedDocuments()
    {
        return $this->hasMany(PatientDocumentRequestSharedDocument::class, 'document_request_id');
    }

    public function getPublicResponse()
    {
        $this->load([
            'patient:id,first_name,last_name,email,secondary_email,date_of_birth,home_phone,cell_phone,work_phone,visit_copay,is_payment_forbidden,address,city,state,zip',
        ]);
        $patient = $this->patient->toArray();
        $patient['id'] = encrypt($patient['id']);
        $forms = $this->items()
            ->with('type')
            ->select(['patient_document_request_items.*'])
            ->join('patient_form_types', 'patient_form_types.id', '=', 'patient_document_request_items.form_type_id')
            ->orderBy('patient_form_types.order')
            ->get()
            ->transform(function ($item) {
                $item->request_hash = $this->hash;

                return $item;
            })
            ->toArray();

        return [
            'id' => $this->getKey(),
            'hash' => $this->hash,
            'expiring_at' => is_null($this->expiring_at) ? null : $this->expiring_at->toDateTimeString(),
            'email' => $this->sent_to_email,
            'patient' => $patient,
            'forms' => $forms,
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

    public function getLogData()
    {
        $senderName = optional(optional($this->sender)->provider)->provider_name;
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'patient_name' => optional($this->patient)->getFullName(),
            'sent_by' => $this->sent_by,
            'sender_name' => $senderName,
            'hash' => $this->hash,
            'expiring_at' => $this->expiring_at,
            'sent_to_email' => $this->sent_to_email,
            'retrieve_count' => $this->retrieve_count,
            'last_retrieved_at' => $this->last_retrieved_at,
            'sent_to_phone' => $this->sent_to_phone,
            'sent_to_phone_error' => $this->sent_to_phone_error,
            'mandrill_event_id' => $this->mandrill_event_id,
            'sent_at' => $this->sent_at,
            'delivered_at' => $this->delivered_at,
            'opened_at' => $this->opened_at,
            'deferral_at' => $this->deferral_at,
            'hard_bounced_at' => $this->hard_bounced_at,
            'soft_bounced_at' => $this->soft_bounced_at,
            'bounced_at' => $this->bounced_at,
            'click_at' => $this->click_at,
            'spam_at' => $this->spam_at,
            'unsub_at' => $this->unsub_at,
            'rejected_at' => $this->rejected_at
        ];
    }

    public function getCreateLogMessage()
    {
        return 'PatientDocumentRequest created: ' . $this->getLogMessageIdentifier();
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

        return 'PatientDocumentRequest updated: ' . implode('; ', $messagesList);
    }

    public function getDeleteLogMessage()
    {
        return 'PatientDocumentRequest deleted: ' . $this->getLogMessageIdentifier();
    }

    public function getLogMessageIdentifier()
    {
        return "{$this->id}; {$this->patient_id}; {$this->sent_by};"
            .   Carbon::parse($this->created_at)->toDateString();;
    }

    public function getScalarChangeableFields()
    {
        return [
            'patient_id' => 'Patient id',
            'sent_by' => 'Sent by',
            'hash' => 'Hash',
            'expiring_at' => 'Expiring at',
            'sent_to_email' => 'Sent to email',
            'retrieve_count' => 'Retrieve count',
            'last_retrieved_at' => 'Last retrieved at',
            'sent_to_phone' => 'Sent to phone',
            'sent_to_phone_error' => 'Sent to phone error',
            'mandrill_event_id' => 'Mandrill event id',
            'sent_at' => 'Sent at',
            'delivered_at' => 'Delivered at',
            'opened_at' => 'Opened at',
            'deferral_at' => 'Deferral at',
            'hard_bounced_at' => 'Hard bounced at',
            'soft_bounced_at' => 'Soft bounced at',
            'bounced_at' => 'Bounced at',
            'click_at' => 'Click at',
            'spam_at' => 'Spam at',
            'unsub_at' => 'Unsub at',
            'rejected_at' => 'Rejected at'
        ];
    }
}

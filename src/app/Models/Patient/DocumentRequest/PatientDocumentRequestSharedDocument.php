<?php

namespace App\Models\Patient\DocumentRequest;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Patient\DocumentRequest\PatientDocumentRequestSharedDocument
 *
 * @property int $id
 * @property int $document_request_id
 * @property string $email
 * @property string $hash
 * @property string $password
 * @property \Carbon\Carbon|null $expiring_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\DocumentRequest\PatientDocumentRequestSharedDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\DocumentRequest\PatientDocumentRequestSharedDocument whereDocumentRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\DocumentRequest\PatientDocumentRequestSharedDocument whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\DocumentRequest\PatientDocumentRequestSharedDocument whereExpiringAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\DocumentRequest\PatientDocumentRequestSharedDocument whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\DocumentRequest\PatientDocumentRequestSharedDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\DocumentRequest\PatientDocumentRequestSharedDocument wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\DocumentRequest\PatientDocumentRequestSharedDocument whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \App\Models\Patient\DocumentRequest\PatientDocumentRequest $documentRequest
 */
class PatientDocumentRequestSharedDocument extends Model
{
    protected $fillable = [
        'document_request_id',
        'email',
        'hash',
        'password',
        'expiring_at'
    ];
    
    protected $hidden = [
        'password'
    ];
    
    protected $casts = [
        'document_request_id' => 'int',
    ];
    
    protected $dates = [
        'expiring_at',
    ];
    
    /**
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'hash';
    }
    
    /**
     * @return BelongsTo
     */
    public function documentRequest()
    {
        return $this->belongsTo(PatientDocumentRequest::class, 'document_request_id');
    }
    
    /**
     * @return bool
     */
    public function isExpired()
    {
        return $this->expiring_at->lt(Carbon::now());
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
        return [
            'id' => $this->id,
            'document_request_id' => $this->document_request_id,
            'email' => $this->email,
            'hash' => $this->hash,
            'password' => $this->password,
            'expiring_at' => $this->expiring_at,
        ];
    }

    public function getCreateLogMessage()
    {
        return 'Document created: ' . $this->getLogMessageIdentifier();
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

        return 'Document updated: ' . implode('; ', $messagesList);
    }

    public function getDeleteLogMessage()
    {
        return 'Document deleted: ' . $this->getLogMessageIdentifier();
    }

    public function getLogMessageIdentifier()
    {
        return "{$this->id}; {$this->document_request_id}; {$this->hash}; "
            . Carbon::parse($this->created_at)->toDateTimeString();
    }

    public function getScalarChangeableFields()
    {
        return [
            'document_request_id' => 'Document request id',
            'email' => 'Email',
            'hash' => 'Hash',
            'password' => 'Password',
            'expiring_at' => 'Expiring at',
        ];
    }
}

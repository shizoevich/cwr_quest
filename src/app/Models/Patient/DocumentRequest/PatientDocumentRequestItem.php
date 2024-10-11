<?php

namespace App\Models\Patient\DocumentRequest;

use App\PatientDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientDocumentRequestItem extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'request_id',
        'form_type_id',
        'metadata',
        'comment',
        'filled_at'
    ];
    
    protected $dates = [
        'filled_at'
    ];
    
    protected $casts = [
        'request_id' => 'int',
        'form_type_id' => 'int',
        'metadata' => 'array',
    ];
    
    /**
     * @return BelongsTo
     */
    public function request()
    {
        return $this->belongsTo(PatientDocumentRequest::class, 'request_id', 'id');
    }
    
    /**
     * @return BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(PatientFormType::class, 'form_type_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany(PatientDocument::class, 'document_request_item_id', 'id');
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
            'request_id' => $this->request_id,
            'form_type_id' => $this->form_type_id,
            'form_type_name' => optional($this->type)->name,
            'metadata' => $this->metadata,
            'filled_at' => $this->filled_at
        ];
    }

    public function getCreateLogMessage()
    {
        return 'PatientDocumentRequestItem created: ' . $this->getLogMessageIdentifier();
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

        return 'PatientDocumentRequestItem updated: ' . implode('; ', $messagesList);
    }

    public function getDeleteLogMessage()
    {
        return 'PatientDocumentRequestItem deleted: ' . $this->getLogMessageIdentifier();
    }

    public function getLogMessageIdentifier()
    {
        return "{$this->id}; {$this->request_id}; {$this->form_type_id};";
    }

    public function getScalarChangeableFields()
    {
        return [
            'request_id' => 'Request id',
            'form_type_id' => 'Form type id',
            'metadata' => 'Metadata',
            'filled_at' => 'Filled at'
        ];
    }
}

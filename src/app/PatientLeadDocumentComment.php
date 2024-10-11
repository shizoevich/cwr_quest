<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientLeadDocumentComment extends Model
{
    protected $table = 'patient_lead_document_comments';

    protected $casts = [
        'is_system_comment' => 'boolean'
    ];

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(UserMeta::class, 'admin_id', 'user_id');
    }

    public function document()
    {
        return $this->belongsTo(PatientLeadDocument::class, 'patient_lead_documents_id', 'id');
    }
}

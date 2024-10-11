<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientLeadDocumentUploadInfo extends Model
{
    protected $table = 'patient_lead_document_upload_info';

    protected $fillable = [
        'user_id',
        'patient_lead_document_id',
        'document_model',
        'client_ip',
        'client_user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}

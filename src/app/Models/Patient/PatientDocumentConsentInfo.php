<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Model;

class PatientDocumentConsentInfo extends Model
{
    protected $table = 'patient_document_consent_info';

    protected $fillable = [
        'patient_document_id',
        'allow_mailing',
        'allow_home_phone_call',
        'allow_mobile_phone_call',
        'allow_mobile_send_messages',
        'allow_work_phone_call',
    ];
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PatientDocumentDownloadInfo
 *
 * @property int $id
 * @property int $patient_document_shared_id
 * @property string $client_ip
 * @property string $client_user_agent
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\PatientDocumentShared $patientDocumentShared
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentDownloadInfo whereClientIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentDownloadInfo whereClientUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentDownloadInfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentDownloadInfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentDownloadInfo wherePatientDocumentSharedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentDownloadInfo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PatientDocumentDownloadInfo extends Model
{
    protected $table = 'patient_document_download_info';

    public function patientDocumentShared() {
        return $this->belongsTo(PatientDocumentShared::class, 'patient_document_shared_id');
    }

}

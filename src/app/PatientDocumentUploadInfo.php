<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PatientDocumentUploadInfo
 *
 * @property int $id
 * @property int $user_id
 * @property int $patient_document_id
 * @property string $document_model
 * @property string $client_ip
 * @property string $client_user_agent
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentUploadInfo whereClientIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentUploadInfo whereClientUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentUploadInfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentUploadInfo whereDocumentModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentUploadInfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentUploadInfo wherePatientDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentUploadInfo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentUploadInfo whereUserId($value)
 * @mixin \Eloquent
 */
class PatientDocumentUploadInfo extends Model
{
    protected $table = 'patient_document_upload_info';

    protected $fillable = [
        'user_id',
        'patient_document_id',
        'document_model',
        'client_ip',
        'client_user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}

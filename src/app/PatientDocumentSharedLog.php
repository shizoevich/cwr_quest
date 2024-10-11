<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PatientDocumentSharedLog
 *
 * @property int $id
 * @property int $patient_document_shared_id
 * @property int $shared_document_statuses_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\PatientDocumentShared $patientDocumentShared
 * @property-read \App\SharedDocumentStatus $sharedStatus
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentSharedLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentSharedLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentSharedLog wherePatientDocumentSharedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentSharedLog whereSharedDocumentStatusesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentSharedLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PatientDocumentSharedLog extends Model
{
    protected $table = 'patient_document_shared_logs';

    public function patientDocumentShared() {
        return $this->belongsTo(PatientDocumentShared::class, 'patient_document_shared_id');
    }

    public function sharedStatus() {
        return $this->belongsTo(SharedDocumentStatus::class, 'shared_document_statuses_id');
    }
}

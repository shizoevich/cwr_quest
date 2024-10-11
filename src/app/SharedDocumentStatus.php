<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\SharedDocumentStatus
 *
 * @property int $id
 * @property string $status
 * @property-read \App\PatientDocumentShared $patientDocumentShared
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SharedDocumentStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SharedDocumentStatus whereStatus($value)
 * @mixin \Eloquent
 */
class SharedDocumentStatus extends Model
{
    protected $table = 'shared_document_statuses';

    public function patientDocumentShared() {
        return $this->hasOne(PatientDocumentShared::class, 'shared_document_methods_id');
    }
}

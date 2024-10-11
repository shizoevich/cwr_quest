<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\PatientDocument;

class TridiuumPatientDocument extends Model
{
    protected $fillable = [
        'external_id',
        'internal_id',
        'tridiuum_patient_id',
        'type_id',
        'is_downloaded',
    ];

    protected $casts = [
        'is_downloaded' => 'bool',
        'internal_id'   => 'int',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patientDocument()
    {
        return $this->belongsTo(PatientDocument::class, 'internal_id', 'id');
    }
}

<?php

namespace App\Models\Patient\Lead;

use Illuminate\Database\Eloquent\Model;

class PatientLeadDiagnose extends Model
{
    protected $fillable = [
        'patient_lead_id',
        'diagnose_id'
    ];

    protected $casts = [
        'patient_lead_id' => 'int',
        'diagnose_id' => 'int',
    ];
}

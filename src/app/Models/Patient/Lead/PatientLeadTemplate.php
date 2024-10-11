<?php

namespace App\Models\Patient\Lead;

use Illuminate\Database\Eloquent\Model;

class PatientLeadTemplate extends Model
{
    protected $fillable = [
        'patient_lead_id',
        'position',
        'pos',
        'cpt',
        'modifier_a',
        'modifier_b',
        'modifier_c',
        'modifier_d',
        'diagnose_pointer',
        'charge',
        'days_or_units',
    ];

    protected $casts = [
        'patient_lead_id' => 'int',
        'days_or_units' => 'int',
        'charge' => 'float',
    ];
}

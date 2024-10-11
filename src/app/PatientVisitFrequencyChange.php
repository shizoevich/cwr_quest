<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientVisitFrequencyChange extends Model
{
    protected $table = 'patients_visit_frequency_changes';

    protected $fillable = ['patient_id', 'old_visit_frequency_id', 'new_visit_frequency_id', 'changed_by', 'comment'];

    
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function oldVisitFrequency()
    {
        return $this->belongsTo(PatientVisitFrequency::class, 'old_visit_frequency_id');
    }

    public function newVisitFrequency()
    {
        return $this->belongsTo(PatientVisitFrequency::class, 'new_visit_frequency_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(UserMeta::class, 'changed_by');
    }
}

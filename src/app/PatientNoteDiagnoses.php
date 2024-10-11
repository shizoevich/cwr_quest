<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientNoteDiagnoses extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    public $table = 'patient_note_diagnoses';

    /**
     * @var array
     */
    protected $fillable = [
        'patient_note_id', 'diagnose_id',
    ];

    public function getLogData()
    {
        return [
            'id' => $this->id,
            'patient_note_id' => $this->patient_note_id,
            'diagnose_id' => $this->diagnose_id
        ];
    }

    public function getCreateLogMessage()
    {
        return 'PatientNoteDiagnose created: ' . $this->getLogMessageIdentifier();
    }

    public function getDeleteLogMessage()
    {
        return 'PatientNoteDiagnose deleted: ' . $this->getLogMessageIdentifier();
    }

    public function getLogMessageIdentifier()
    {
        return "{$this->id}; {$this->patient_note_id}; {$this->diagnose_id};";
    }
}

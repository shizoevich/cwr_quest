<?php

namespace App\Models\Patient\Visit;

use Illuminate\Database\Eloquent\Model;

class PatientVisitDiagnose extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'visit_id',
        'diagnose_id',
    ];

    public function getDirtyWithOriginal()
    {
        $result = [];
        $dirtyFields = $this->getDirty();

        foreach ($dirtyFields as $fieldName => $fieldValue) {
            $result[$fieldName] = [
                'prev' => $this->getOriginal($fieldName),
                'curr' => $fieldValue,
            ];
        }

        return $result;
    }

    public function getLogData()
    {
        return [
            'id' => $this->id,
            'visit_id' => $this->visit_id,
            'diagnose_id' => $this->diagnose_id,
        ];
    }

    public function getCreateLogMessage()
    {
        return 'PatientVisitDiagnose created: ' . $this->getLogMessageIdentifier();
    }

    public function getUpdateLogMessage($dirtyFields = null)
    {
        if (empty($dirtyFields)) {
            $dirtyFields = $this->getDirtyWithOriginal();
        }

        $messagesList = [];

        foreach ($this->getScalarChangeableFields() as $fieldName => $message) {
            if (isset($dirtyFields[$fieldName])) {
                $messagesList[] = "$message changed from '{$dirtyFields[$fieldName]['prev']}' to '{$dirtyFields[$fieldName]['curr']}'";
            }
        }

        return 'PatientVisitDiagnose updated: ' . implode('; ', $messagesList);
    }

    public function getDeleteLogMessage()
    {
        return 'PatientVisitDiagnose deleted: ' . $this->getLogMessageIdentifier();
    }

    public function getLogMessageIdentifier()
    {
        return "{$this->id}; {$this->visit_id}; '{$this->diagnose_id}'";
    }

    public function getScalarChangeableFields()
    {
        return [

        ];
    }
}

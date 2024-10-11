<?php

namespace App\Models\Patient;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * \App\Models\Patient\PatientDiagnose
 *
 * @property int $id
 * @property int $patient_id
 * @property int $diagnose_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientDiagnose whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientDiagnose whereDiagnoseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientDiagnose whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientDiagnose wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientDiagnose whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PatientDiagnose extends Model
{
    protected $fillable = [
        'patient_id',
        'diagnose_id'
    ];
    
    protected $casts = [
        'patient_id' => 'int',
        'diagnose_id' => 'int',
    ];

    public function getCreateLogMessage(): string
    {
        return 'Patient diagnose: ' . $this->getLogMessageIdentifier();
    }

    public function getUpdateLogMessage(array $dirtyFields = null): string
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

        return 'Patient diagnose updated: ' . implode('; ', $messagesList);
    }

    public function getDeleteLogMessage(): string
    {
        return 'Patient diagnose deleted: ' . $this->getLogMessageIdentifier();
    }

    public function getDirtyWithOriginal(): array
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

    public function getLogData(): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'diagnose_id' => $this->diagnose_id,
        ];
    }

    public function getScalarChangeableFields(): array
    {
        return [
            'patient_id' => 'Patient id',
            'diagnose_id' => 'Diagnose id',
        ];
    }

    public function getLogMessageIdentifier(): string
    {
        return "{$this->id}; {$this->patient_id}; '{$this->diagnose_id}'; "
            . Carbon::parse($this->created_at)->toDateTimeString();
    }
}

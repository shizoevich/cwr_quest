<?php

namespace App\Models\Patient;

use App\Patient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * \App\Models\Patient\PatientTemplate
 *
 * @property int $id
 * @property int $patient_id
 * @property int $position
 * @property string|null $pos
 * @property string|null $cpt
 * @property string|null $modifier_a
 * @property string|null $modifier_b
 * @property string|null $modifier_c
 * @property string|null $modifier_d
 * @property string|null $diagnose_pointer
 * @property float $charge
 * @property int $days_or_units
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Patient $patient
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTemplate whereCpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTemplate whereDaysOrUnits($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTemplate whereDiagnosePointer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTemplate whereLineCharges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTemplate whereModifierA($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTemplate whereModifierB($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTemplate whereModifierC($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTemplate whereModifierD($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTemplate wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTemplate wherePos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTemplate wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTemplate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PatientTemplate extends Model
{
    protected $fillable = [
        'patient_id',
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
        'patient_id' => 'int',
        'days_or_units' => 'int',
        'charge' => 'float',
    ];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

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
            'patient_id' => $this->patient_id,
            'patient_name' => optional($this->patient)->getFullName(),
            'position' => $this->position,
            'pos' => $this->pos,
            'patient_insurances_procedure_id' => $this->patient_insurances_procedure_id,
            'cpt' => $this->cpt,
            'modifier_a' => $this->modifier_a,
            'modifier_b' => $this->modifier_b,
            'modifier_c' => $this->modifier_c,
            'modifier_d' => $this->modifier_d,
            'diagnose_pointer' => $this->diagnose_pointer,
            'charge' => $this->charge,
            'days_or_units' => $this->days_or_units,
        ];
    }

    public function getCreateLogMessage()
    {
        return 'Template created: ' . $this->getLogMessageIdentifier();
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

        return 'Template updated: ' . implode('; ', $messagesList);
    }

    public function getDeleteLogMessage()
    {
        return 'Template deleted: ' . $this->getLogMessageIdentifier();
    }

    public function getLogMessageIdentifier()
    {
        return "{$this->id}; {$this->patient_id}; "
            . Carbon::parse($this->created_at)->toDateTimeString();
    }

    public function getScalarChangeableFields()
    {
        return [
            'patient_id' => 'Patient id',
            'position' => 'Position',
            'pos' => 'Pos',
            'patient_insurances_procedure_id' => 'Patient insurances procedure id',
            'cpt' => 'cpt',
            'modifier_a' => 'Modifier A',
            'modifier_b' => 'Modifier B',
            'modifier_c' => 'Modifier C',
            'modifier_d' => 'Modifier D',
            'diagnose_pointer' => 'Diagnose pointer',
            'charge' => 'Charge',
            'days_or_units' => 'Days or units',
        ];
    }
}

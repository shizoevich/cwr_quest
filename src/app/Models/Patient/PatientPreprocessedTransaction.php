<?php

namespace App\Models\Patient;

use App\Patient;
use App\Provider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\Patient\PatientPreprocessedTransaction
 *
 * @property int $id
 * @property int $patient_id
 * @property int $balance_before_transaction
 * @property int $balance_after_transaction
 * @property int|null $transactionable_id
 * @property string|null $transactionable_type
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Patient $patient
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $transactionable
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientPreprocessedTransaction whereBalanceAfterTransaction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientPreprocessedTransaction whereBalanceBeforeTransaction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientPreprocessedTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientPreprocessedTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientPreprocessedTransaction wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientPreprocessedTransaction whereTransactionableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientPreprocessedTransaction whereTransactionableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientPreprocessedTransaction whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PatientPreprocessedTransaction extends Model 
{
    protected $guarded = [];

    protected $dates = [
        'created_at',
        'updated_at',
        'detached_at',
    ];

    public static function getLast($patientId)
    {
        return static::where('patient_id', $patientId)
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function transactionable()
    {
        return $this->morphTo();
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
        try {
            return [
                'id' => $this->id,
                'patient_id' => $this->patient_id,
                'patient_name' => optional($this->patient)->getFullName(),
                'balance_before_transaction' => $this->balance_before_transaction,
                'balance_after_transaction' => $this->balance_after_transaction,
                'transactionable_id' => $this->transactionable_id,
                'transactionable_type' => $this->transactionable_type,
                'detached_at' => optional($this->detached_at)->toDateTimeString(),
                'created_at' => $this->created_at->toDateTimeString(),
                'updated_at' => $this->updated_at->toDateTimeString(),
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getCreateLogMessage()
    {
        try {
            return 'PatientPreprocessedTransaction created: ' . $this->getLogMessageIdentifier();
        } catch (\Exception $e) {
            return 'PatientPreprocessedTransaction created';
        }
    }

    public function getUpdateLogMessage($dirtyFields = null): string
    {
        try {
            if (empty($dirtyFields)) {
                $dirtyFields = $this->getDirtyWithOriginal();
            }

            $messagesList = [];

            foreach ($this->getScalarChangeableFields() as $fieldName => $message) {
                if (isset($dirtyFields[$fieldName])) {
                    $messagesList[] = "$message changed from '{$dirtyFields[$fieldName]['prev']}' to '{$dirtyFields[$fieldName]['curr']}'";
                }
            }

            if (isset($dirtyFields['patient_id'])) {
                $prevPatient = Patient::find($dirtyFields['patient_id']['prev']);
                $currPatient = Patient::find($dirtyFields['patient_id']['curr']);
                $messagesList[] = "Patient id changed from '{$prevPatient->id}' to '{$currPatient->id}'";
                $messagesList[] = "Patient name changed from '{$prevPatient->getFullName()}' to '{$currPatient->getFullName()}'";
            }

            if (isset($dirtyFields['created_at'])) {
                $prevCreatedAt = $dirtyFields['created_at']['prev']->toDateTimeString();
                $currCreatedAt = $dirtyFields['created_at']['curr']->toDateTimeString();
                $messagesList[] = "Created at changed from '{$prevCreatedAt}' to '{$currCreatedAt}'";
            }

            if (isset($dirtyFields['updated_at'])) {
                $prevUpdatedAt = $dirtyFields['updated_at']['prev']->toDateTimeString();
                $currUpdatedAt = $dirtyFields['updated_at']['curr']->toDateTimeString();
                $messagesList[] = "Updated at changed from '{$prevUpdatedAt}' to '{$currUpdatedAt}'";
            }

            if (isset($dirtyFields['detached_at'])) {
                $prevDetachedAt = optional($dirtyFields['detached_at']['prev'])->toDateTimeString();
                $currDetachedAt = optional($dirtyFields['detached_at']['curr'])->toDateTimeString();
                $messagesList[] = "Detached at changed from '{$prevDetachedAt}' to '{$currDetachedAt}'";
            }

            return 'PatientPreprocessedTransaction updated: ' . implode('; ', $messagesList);
        } catch (\Exception $e) {
            return 'PatientPreprocessedTransaction updated';
        }
    }

    public function getDeleteLogMessage(): string
    {
        try {
            return 'PatientPreprocessedTransaction deleted: ' . $this->getLogMessageIdentifier();
        } catch (\Exception $e) {
            return 'PatientPreprocessedTransaction deleted';
        }
    }

    public function getLogMessageIdentifier(): string
    {
        $patientName = optional($this->patient)->getFullName();

        return "{$this->id}; {$this->patient_id}; '{$patientName}'; " . $this->created_at->toDateTimeString();
    }

    public function getScalarChangeableFields(): array
    {
        return [
            'balance_before_transaction' => 'Balance before transaction',
            'balance_after_transaction' => 'Balance after transaction',
            'transactionable_id' => 'Transactionable id',
            'transactionable_type' => 'Transactionable type',
        ];
    }
}

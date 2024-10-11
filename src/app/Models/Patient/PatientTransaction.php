<?php

namespace App\Models\Patient;

use App\Patient;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Patient\PatientTransaction
 *
 * @property int $id
 * @property int $patient_id
 * @property int $balance_before_transaction
 * @property int $balance_after_transaction
 * @property int $transactionable_id
 * @property string $transactionable_type
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Patient $patient
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $transactionable
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTransaction whereBalanceAfterTransaction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTransaction whereBalanceBeforeTransaction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTransaction wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTransaction whereTransactionableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTransaction whereTransactionableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTransaction whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PatientTransaction extends Model
{
    protected $table = 'patient_transactions';

    protected $guarded = [];

    protected $dates = [
        'created_at',
        'updated_at',
        'detached_at',
    ];

    public static function getLast($patientId) {
        return static::where('patient_id', $patientId)
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient() {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function transactionable() {
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
            return 'PatientTransaction created: ' . $this->getLogMessageIdentifier();
        } catch (\Exception $e) {
            return 'PatientTransaction created';
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
                $messagesList[] = "Provider id changed from '{$prevPatient->id}' to '{$currPatient->id}'";
                $messagesList[] = "Provider name changed from '{$prevPatient->getFullName()}' to '{$currPatient->getFullName()}'";
            }

            if (isset($dirtyFields['created_at'])) {
                $prevCreatedAt = optional($dirtyFields['created_at']['prev'])->toDateTimeString();
                $currCreatedAt = optional($dirtyFields['created_at']['curr'])->toDateTimeString();
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

            return 'PatientTransaction updated: ' . implode('; ', $messagesList);
        } catch (\Exception $e) {
            return 'PatientTransaction updated';
        }
    }

    public function getDeleteLogMessage(): string
    {
        try {
            return 'PatientTransaction deleted: ' . $this->getLogMessageIdentifier();
        } catch (\Exception $e) {
            return 'PatientTransaction deleted';
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

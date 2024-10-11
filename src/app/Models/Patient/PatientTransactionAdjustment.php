<?php

namespace App\Models\Patient;

use App\Patient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Patient\PatientTransactionAdjustment
 *
 * @property int $id
 * @property int $patient_id
 * @property int $amount
 * @property \Carbon\Carbon|null $transaction_date
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $processed_at
 * @property string $comment
 * @property int $user_id
 * @property-read \App\Models\Patient\PatientTransaction $patientTransaction
 * @property-read \App\Patient $patient
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTransactionAdjustment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTransactionAdjustment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTransactionAdjustment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTransactionAdjustment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTransactionAdjustment wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTransactionAdjustment whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTransactionAdjustment whereTransactionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTransactionAdjustment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTransactionAdjustment whereUserId($value)
 * @mixin \Eloquent
 * @property string|null $preprocessed_at
 * @property-read \App\Models\Patient\PatientPreprocessedTransaction $patientPreprocessedTransaction
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientTransactionAdjustment wherePreprocessedAt($value)
 */
class PatientTransactionAdjustment extends Model
{
    protected $table = 'patient_transaction_adjustments';

    protected $guarded = [];

    protected $dates = [
        'transaction_date',
        'created_at',
        'updated_at',
        'processed_at',
    ];

    protected $casts = [
        'patient_id' => 'integer',
        'amount'     => 'integer',
        'user_id'    => 'integer',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function patientTransaction()
    {
        return $this->morphOne(PatientTransaction::class, 'transactionable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function patientPreprocessedTransaction()
    {
        return $this->morphOne(PatientPreprocessedTransaction::class, 'transactionable');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public static function addAdjustment($patientId, $amount, $comment, $userId)
    {
        $now = Carbon::now();
        static::create([
            'patient_id'       => $patientId,
            'amount'           => $amount,
            'transaction_date' => $now,
            'comment'          => $comment,
            'user_id'          => $userId,
        ]);
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
                'amount' => $this->amount,
                'transaction_date' => $this->transaction_date,
                'user_id' => $this->user_id,
                'comment' => $this->comment,
                'created_at' => $this->created_at->toDateTimeString(),
                'updated_at' => $this->updated_at->toDateTimeString(),
                'processed_at' => optional($this->processed_at)->toDateTimeString(),
                'preprocessed_at' => $this->preprocessed_at,
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getCreateLogMessage()
    {
        try {
            return 'PatientTransactionAdjustment created: ' . $this->getLogMessageIdentifier();
        } catch (\Exception $e) {
            return 'PatientTransactionAdjustment created';
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
                $prevCreatedAt = optional($dirtyFields['created_at']['prev'])->toDateTimeString();
                $currCreatedAt = optional($dirtyFields['created_at']['curr'])->toDateTimeString();
                $messagesList[] = "Created at changed from '{$prevCreatedAt}' to '{$currCreatedAt}'";
            }

            if (isset($dirtyFields['updated_at'])) {
                $prevUpdatedAt = $dirtyFields['updated_at']['prev']->toDateTimeString();
                $currUpdatedAt = $dirtyFields['updated_at']['curr']->toDateTimeString();
                $messagesList[] = "Updated at changed from '{$prevUpdatedAt}' to '{$currUpdatedAt}'";
            }

            if (isset($dirtyFields['processed_at'])) {
                $prevProcessedAt = optional($dirtyFields['processed_at']['prev'])->toDateTimeString();
                $currProcessedAt = optional($dirtyFields['processed_at']['curr'])->toDateTimeString();
                $messagesList[] = "Processed at changed from '{$prevProcessedAt}' to '{$currProcessedAt}'";
            }

            return 'PatientTransactionAdjustment updated: ' . implode('; ', $messagesList);
        } catch (\Exception $e) {
            return 'PatientTransactionAdjustment updated';
        }
    }

    public function getDeleteLogMessage(): string
    {
        try {
            return 'PatientTransactionAdjustment deleted: ' . $this->getLogMessageIdentifier();
        } catch (\Exception $e) {
            return 'PatientTransactionAdjustment deleted';
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
            'amount' => 'Amount',
            'transaction_date' => 'Transaction date',
            'user_id' => 'User id',
            'comment' => 'Comment',
            'preprocessed_at' => 'Preprocessed at',
        ];
    }
}

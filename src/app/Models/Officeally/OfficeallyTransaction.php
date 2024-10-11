<?php

namespace App\Models\Officeally;

use App\Models\Patient\PatientTransaction;
use App\Patient;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Officeally\OfficeallyTransaction
 *
 * @property int $id
 * @property string $external_id
 * @property int $patient_id
 * @property int $transaction_type_id
 * @property int $payment_amount
 * @property int $applied_amount
 * @property \Carbon\Carbon|null $transaction_date
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $processed_at
 * @property \Carbon\Carbon|null $start_posting_date
 * @property-read \App\Patient $patient
 * @property-read \App\Models\Patient\PatientTransaction $patientTransaction
 * @property-read \App\Models\Officeally\OfficeallyTransactionType $transactionType
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Officeally\OfficeallyTransaction whereAppliedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Officeally\OfficeallyTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Officeally\OfficeallyTransaction whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Officeally\OfficeallyTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Officeally\OfficeallyTransaction wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Officeally\OfficeallyTransaction wherePaymentAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Officeally\OfficeallyTransaction whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Officeally\OfficeallyTransaction whereStartPostingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Officeally\OfficeallyTransaction whereTransactionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Officeally\OfficeallyTransaction whereTransactionTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Officeally\OfficeallyTransaction whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \Carbon\Carbon|null $preprocessed_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Officeally\OfficeallyTransaction wherePreprocessedAt($value)
 */
class OfficeallyTransaction extends Model
{
    protected $table = 'officeally_transactions';

    protected $guarded = [];

    protected $dates = [
        'transaction_date',
        'created_at',
        'updated_at',
        'processed_at',
        'start_posting_date',
        'preprocessed_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'patient_id' => 'integer',
        'transaction_type_id' => 'integer',
        'payment_amount' => 'integer',
        'applied_amount' => 'integer',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient() {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transactionType() {
        return $this->belongsTo(OfficeallyTransactionType::class, 'transaction_type_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function patientTransaction() {
        return $this->morphOne(PatientTransaction::class, 'transactionable');
    }
}

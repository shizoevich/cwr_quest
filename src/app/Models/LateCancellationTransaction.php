<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Patient\PatientTransaction;

class LateCancellationTransaction extends Model
{
    protected $table = 'late_cancellation_transactions';

    protected $guarded = [];

    protected $dates = [
        'transaction_date',
        'created_at',
        'updated_at',
        'processed_at',
        'preprocessed_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'appointment_id' => 'integer',
        'payment_amount' => 'integer',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function patientTransaction() {
        return $this->morphOne(PatientTransaction::class, 'transactionable');
    }
}

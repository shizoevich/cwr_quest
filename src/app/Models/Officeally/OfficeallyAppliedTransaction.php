<?php

namespace App\Models\Officeally;

use Illuminate\Database\Eloquent\Model;
use App\PatientVisit;
use App\Models\Officeally\OfficeallyAppliedTransactionType;

class OfficeallyAppliedTransaction extends Model
{
    protected $fillable = [
        'external_id',
        'applied_transaction_type_id',
        'patient_visit_id',
        'applied_amount',
        'applied_date',
        'transaction_date',
    ];

    protected $dates = [
        'applied_date',
        'transaction_date',
    ];

    public function getAppliedAmountAttribute($value)
    {
        return money_round($value / 100);
    }

    public function setAppliedAmountAttribute($value)
    {
        $this->attributes['applied_amount'] = intval(floatval($value) * 100);
    }

    public function scopeOnlyPayments($query)
    {
        $insurancePaymentId = OfficeallyAppliedTransactionType::getInsurancePaymentId();
        $patientPaymentId = OfficeallyAppliedTransactionType::getPatientPaymentId();
        
        return $query->whereIn('applied_transaction_type_id', [$insurancePaymentId, $patientPaymentId]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function visit() {
        return $this->belongsTo(PatientVisit::class, 'patient_visit_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transactionType() {
        return $this->belongsTo(OfficeallyAppliedTransactionType::class, 'applied_transaction_type_id', 'id');
    }
}

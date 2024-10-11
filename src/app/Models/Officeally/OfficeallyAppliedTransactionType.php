<?php

namespace App\Models\Officeally;

use Illuminate\Database\Eloquent\Model;
use App\Models\Officeally\OfficeallyAppliedTransaction;

class OfficeallyAppliedTransactionType extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions() {
        return $this->hasMany(OfficeallyAppliedTransaction::class, 'applied_transaction_type_id', 'id');
    }

    public static function getInsurancePaymentId()
    {
        return \Cache::rememberForever('applied_transaction_types:insurance_payment', function () {
            return static::where('name', 'Insurance Payment')->first()['id'];
        });
    }

    public static function getPatientPaymentId()
    {
        return \Cache::rememberForever('applied_transaction_types:patient_payment', function () {
            return static::where('name', 'Patient Payment')->first()['id'];
        });
    }

    public static function getAdjustmentId()
    {
        return \Cache::rememberForever('applied_transaction_types:adjustment', function () {
            return static::where('name', 'Adjustment')->first()['id'];
        });
    }
}

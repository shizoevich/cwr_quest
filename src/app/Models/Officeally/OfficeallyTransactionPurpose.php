<?php

namespace App\Models\Officeally;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Officeally\OfficeallyTransactionPurpose
 *
 * @property int $id
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Officeally\OfficeallyTransaction[] $transactions
 * @mixin \Eloquent
 */
class OfficeallyTransactionPurpose extends Model
{
    public const PURPOSE_COPAY = 'Co-Pay';
    public const PURPOSE_DEDUCTIBLE = 'Deductible';
    public const PURPOSE_SELF_PAY = 'Self-Pay';

    protected $table = 'officeally_transaction_purposes';

    protected $fillable = [
        'name',
        'description'
    ];

    public $timestamps = false;

    public function transactions(): HasMany
    {
        return $this->hasMany(OfficeallyTransaction::class, 'transaction_purpose_id', 'id');
    }

    public static function getTransactionPurposeIdByName(string $name)
    {
        $transactionPurpose = static::where('name', $name)->first();
        return optional($transactionPurpose)->id;
    }

    public function getCopayId(): int
    {
        return \Cache::rememberForever('officeally_transaction_purposes:copay', function () {
            return self::where('name', self::PURPOSE_COPAY)->first()->id;
        });
    }

    public function getSelfPayId(): int
    {
        return \Cache::rememberForever('officeally_transaction_purposes:self_pay', function () {
            return self::where('name', self::PURPOSE_SELF_PAY)->first()->id;
        });
    }

    public function getDeductibleId(): int
    {
        return \Cache::rememberForever('officeally_transaction_purposes:deductible', function () {
            return self::where('name', self::PURPOSE_DEDUCTIBLE)->first()->id;
        });
    }
}

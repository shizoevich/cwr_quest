<?php

namespace App\Models\Officeally;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Officeally\OfficeallyTransactionType
 *
 * @property int $id
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Officeally\OfficeallyTransaction[] $transactions
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Officeally\OfficeallyTransactionType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Officeally\OfficeallyTransactionType whereName($value)
 * @mixin \Eloquent
 */
class OfficeallyTransactionType extends Model
{
    public const CASH_PAYMENT_METHOD = 'cash';
    public const CHECK_PAYMENT_METHOD = 'check';
    public const CREDIT_CARD_PAYMENT_METHOD = 'credit card';

    protected $table = 'officeally_transaction_types';

    protected $guarded = [];

    public $timestamps = false;

    public static function getCashType()
    {
        return \Cache::rememberForever('officeally_transaction_type:cash', function () {
            return static::where('name', 'Cash')->first();
        });
    }

    public static function getCheckType()
    {
        return \Cache::rememberForever('officeally_transaction_type:check', function () {
            return static::where('name', 'Check')->first();
        });
    }

    public static function getCreditCardType()
    {
        return \Cache::rememberForever('officeally_transaction_type:credit_card', function () {
            return static::where('name', 'Credit Card')->first();
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions() {
        return $this->hasMany(OfficeallyTransaction::class, 'transaction_type_id', 'id');
    }
}

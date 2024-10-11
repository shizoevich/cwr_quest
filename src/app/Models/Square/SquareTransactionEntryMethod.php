<?php

namespace App\Models\Square;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Square\SquareTransactionEntryMethod
 *
 * @property int $id
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Square\SquareTransaction[] $transactions
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareTransactionEntryMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareTransactionEntryMethod whereName($value)
 * @mixin \Eloquent
 */
class SquareTransactionEntryMethod extends Model
{
    protected $table = 'square_transaction_entry_methods';

    public $timestamps = false;

    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions() {
        return $this->hasMany(SquareTransaction::class, 'entry_method_id', 'id');
    }
}

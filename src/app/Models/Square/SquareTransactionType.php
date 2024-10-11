<?php

namespace App\Models\Square;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Square\SquareTransactionType
 *
 * @property int $id
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Square\SquareTransaction[] $transactions
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareTransactionType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareTransactionType whereName($value)
 * @mixin \Eloquent
 */
class SquareTransactionType extends Model
{
    protected $table = 'square_transaction_types';

    public $timestamps = false;

    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions() {
        return $this->hasMany(SquareTransaction::class, 'transaction_type_id', 'id');
    }
}

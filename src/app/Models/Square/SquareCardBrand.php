<?php

namespace App\Models\Square;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Square\SquareCardBrand
 *
 * @property int $id
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareCardBrand whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareCardBrand whereName($value)
 * @mixin \Eloquent
 */
class SquareCardBrand extends Model
{
    protected $table = 'square_card_brands';

    protected $guarded = [];

    public $timestamps = false;
}

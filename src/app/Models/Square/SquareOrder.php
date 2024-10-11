<?php

namespace App\Models\Square;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SquareOrder extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'external_id',
        'location_id',
        'customer_id',
        'catalog_item_id',
        'amount_money',
        'order_date'
    ];
}

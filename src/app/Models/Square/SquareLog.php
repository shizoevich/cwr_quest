<?php

namespace App\Models\Square;

use Illuminate\Database\Eloquent\Model;

class SquareLog extends Model
{
    const ACTION_CREATE_CUSTOMER      = 'create_customer';
    const ACTION_UPDATE_CUSTOMER      = 'update_customer';
    const ACTION_CREATE_CUSTOMER_CARD = 'create_customer_card';
    const ACTION_CREATE_ORDER         = 'create_order';
    const ACTION_GET_ORDER            = 'get_order';
    const ACTION_CANCEL_ORDER         = 'cancel_order';
    const ACTION_CREATE_INVOICE       = 'create_invoice';
    const ACTION_PUBLISH_INVOICE      = 'publish_invoice';
    const ACTION_CREATE_PAYMENT       = 'create_payment';
    
    protected $fillable = [
        'is_success',
        'user_id',
        'patient_id',
        'action',
        'request',
        'response',
    ];
    
    protected $casts = [
        'is_success' => 'bool',
        'user_id'    => 'int',
        'patient_id' => 'int',
        'request'    => 'array',
        'response'   => 'array',
    ];
}

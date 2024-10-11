<?php

namespace App\Models\Officeally;

use Illuminate\Database\Eloquent\Model;

class OfficeallyLog extends Model
{
    const ACTION_UPDATE_APPOINTMENT = 1;
    
    const ACTION_ADD_PAYMENT_TO_APPOINTMENT = 2;
    
    const ACTION_DELETE_UPCOMING_APPOINTMENTS = 3;
    
    const ACTION_CREATE_PATIENT = 4;
    
    const ACTION_CREATE_VISIT = 5;
    
    const ACTION_CREATE_CLAIM = 6;
    
    const ACTION_POSTING = 7;
    
    const ACTION_UPDATE_PATIENT = 8;
    
    const ACTION_CREATE_APPOINTMENT = 9;
    
    const ACTION_DELETE_APPOINTMENT = 10;

    const ACTION_POST_ALLERT = 11;

    const ACTION_UPDATE_PROVIDER_IN_CLAIM = 12;
    
    protected $fillable = [
        'action',
        'is_success',
        'message',
        'data',
    ];
    
    protected $casts = [
        'data' => 'array',
        'is_success' => 'bool',
    ];
}

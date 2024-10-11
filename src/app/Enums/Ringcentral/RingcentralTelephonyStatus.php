<?php

namespace App\Enums\Ringcentral;

class RingcentralTelephonyStatus extends AbstractStatus
{
    const STATUS_RINGING = 1;
    
    const STATUS_CALL_CONNECTED = 2;
    
    const STATUS_ON_HOLD = 3;
    
    const STATUS_PARKED_CALL = 4;
    
    const STATUS_NO_CALL = 5;
    
    const MAPPED_STATUSES = [
        'NoCall'        => self::STATUS_NO_CALL,
        'CallConnected' => self::STATUS_CALL_CONNECTED,
        'Ringing'       => self::STATUS_RINGING,
        'OnHold'        => self::STATUS_ON_HOLD,
        'ParkedCall'    => self::STATUS_PARKED_CALL,
    ];
}
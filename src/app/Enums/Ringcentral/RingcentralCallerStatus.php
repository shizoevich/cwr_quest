<?php

namespace App\Enums\Ringcentral;

class RingcentralCallerStatus extends AbstractStatus
{
    /**
     * Connection to the target leg is being established
     */
    const STATUS_IN_PROGRESS = 1;
    
    /**
     * Call party has answered the call
     */
    const STATUS_SUCCESS = 2;
    
    /**
     * The call was terminated (In Progress > Success > Finished)
     */
    const STATUS_FINISHED = 3;
    
    /**
     * Target device is busy
     */
    const STATUS_BUSY = 10;
    
    /**
     * The call has been dropped because of timeout
     */
    const STATUS_NO_ANSWER = 11;
    
    /**
     * RingOut command was canceled by user or RingOut initiated, 1st leg answered,
     * 2nd is ringing, user drops call on the 1st leg - 2nd leg gets 'Rejected'
     */
    const STATUS_REJECTED = 12;
    
    /**
     * Error code received from PSTN or Internal server error
     */
    const STATUS_GENERIC_ERROR = 13;
    
    /**
     * International calling disabled (Call to International number)
     * or Domestic calling disabled (Call with local Country code)
     * or Internal calling disabled (Call within one account)
     */
    const STATUS_INTERNATIONAL_DISABLED = 14;
    
    /**
     * RingOut status was requested for RingOut session which does not exist (e.g was already Closed)
     */
    const STATUS_NO_SESSION_FOUND = 15;
    
    /**
     * RingOut session state is unknown due to internal failure
     */
    const STATUS_INVALID = 16;
    
    const MAPPED_STATUSES = [
        'InProgress'            => self::STATUS_IN_PROGRESS,
        'Busy'                  => self::STATUS_BUSY,
        'NoAnswer'              => self::STATUS_NO_ANSWER,
        'Rejected'              => self::STATUS_REJECTED,
        'Success'               => self::STATUS_SUCCESS,
        'Finished'              => self::STATUS_FINISHED,
        'GenericError'          => self::STATUS_GENERIC_ERROR,
        'InternationalDisabled' => self::STATUS_INTERNATIONAL_DISABLED,
        'NoSessionFound'        => self::STATUS_NO_SESSION_FOUND,
        'Invalid'               => self::STATUS_INVALID,
    ];
}
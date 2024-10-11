<?php

namespace App\Enums\Ringcentral;

class RingcentralCallStatus extends AbstractStatus
{
    /**
     * Connection is being established
     */
    const STATUS_IN_PROGRESS = 1;
    
    /**
     * Both legs connected (Answered)
     */
    const STATUS_SUCCESS = 2;
    
    /**
     * Failure state (one or both legs are in invalid state for call connection)
     */
    const STATUS_CANNOT_REACH = 10;
    
    /**
     * Internal server failure
     */
    const STATUS_NO_ANSWERING_MACHINE = 11;
    
    const MAPPED_STATUSES = [
        'InProgress'         => self::STATUS_IN_PROGRESS,
        'Success'            => self::STATUS_SUCCESS,
        'CannotReach'        => self::STATUS_CANNOT_REACH,
        'NoAnsweringMachine' => self::STATUS_NO_ANSWERING_MACHINE,
    ];
}
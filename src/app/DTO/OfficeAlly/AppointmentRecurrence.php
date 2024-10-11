<?php

namespace App\DTO\OfficeAlly;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * Class AppointmentRecurrence
 * @package App\DTO\OfficeAlly
 */
class AppointmentRecurrence extends DataTransferObject
{
    /**
     * @var int
     */
    public $daysBetweenVisits = 7;
    
    /**
     * @var int
     */
    public $repeat = 0;
}
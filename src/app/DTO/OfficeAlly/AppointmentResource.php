<?php

namespace App\DTO\OfficeAlly;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * Class AppointmentResource
 * @package App\DTO\OfficeAlly
 */
class AppointmentResource extends DataTransferObject
{
    const TYPE_MACHINE = 4;
    
    const TYPE_ROOM = 5;
    
    /**
     * OfficeAlly Resource (Room) Id
     * @var int
     */
    public $id = 0;
    
    /**
     * 0 - not selected
     * 4 - Machine
     * 5 - Room
     *
     * @var int
     */
    public $type = 0;
}
<?php

namespace App\DTO\OfficeAlly;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * Class Appointment
 * @package App\DTO\OfficeAlly
 */
class Appointment extends DataTransferObject
{
    /**
     * OfficeAlly Appointment Id
     * @var int|null
     */
    public $id;
    
    /**
     * Appointment Date
     * @var \Carbon\Carbon
     */
    public $date;
    
    /**
     * OfficeAlly Office Id
     * @var int
     */
    public $officeId;
    
    /**
     * OfficeAlly Patient Id
     * @var int
     */
    public $patientId;
    
    /**
     * @var string e.g. Telehealth, Psychotherapy...
     */
    public $reasonForVisit;
    
    /**
     * OfficeAlly Provider Id
     * @var int
     */
    public $providerId;
    
    /**
     * Visit Length in minutes
     * @var int
     */
    public $visitLength;
    
    /**
     * @var \App\DTO\OfficeAlly\AppointmentResource
     */
    public $resource;
    
    /**
     * OfficeAlly Status Id
     * @see AppointmentStatuses
     * @var int
     */
    public $statusId;
    
    /**
     * @var string|null
     */
    public $notes;
    
    /**
     * @var \App\DTO\OfficeAlly\AppointmentRecurrence|null
     */
    public $recurrence;
}
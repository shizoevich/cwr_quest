<?php

namespace App\Exceptions\Officeally\Appointment;

use App\Exceptions\Officeally\OfficeallyException;

class AppointmentNotDeletedException extends OfficeallyException
{
    /**
     * @var int
     */
    private $appointmentId;
    
    /**
     * AppointmentNotDeletedException constructor.
     *
     * @param int    $appointmentId
     * @param string $message
     */
    public function __construct(int $appointmentId, string $message = '')
    {
        $this->setOriginalMessage($message);
        $this->appointmentId = $appointmentId;
        parent::__construct($message, 0, null);
    }
    
    /**
     * @inheritDoc
     */
    public function getHumanReadableMessage(): string
    {
        return 'Appointment is not deleted (OfficeAlly unavailable). Please try again later.';
    }
    
    public function getStatusCode(): int
    {
        return 409;
    }
}
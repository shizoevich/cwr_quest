<?php

namespace App\Exceptions\Officeally\Appointment;

use App\Exceptions\Officeally\OfficeallyException;

class AppointmentNotFoundException extends OfficeallyException
{
    private $appointmentId;
    
    /**
     * AppointmentNotFoundException constructor.
     *
     * @param        $appointmentId
     * @param string $message
     */
    public function __construct($appointmentId, $message = "")
    {
        $this->appointmentId = $appointmentId;
        if(empty($message)) {
            $message = "Appointment {$appointmentId} not found in OfficeAlly";
        }
        parent::__construct($message, 0, null);
    }
    
    /**
     * @return mixed
     */
    public function getAppointmentId()
    {
        return $this->appointmentId;
    }
    
    public function getHumanReadableMessage(): string
    {
        return 'Appointment not found in OfficeAlly.';
    }
    
    public function getStatusCode(): int
    {
        return 404;
    }
}
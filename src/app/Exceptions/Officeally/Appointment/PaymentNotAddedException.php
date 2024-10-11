<?php

namespace App\Exceptions\Officeally\Appointment;

use App\Exceptions\Officeally\OfficeallyException;

class PaymentNotAddedException extends OfficeallyException
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
            $message = "Payment for appointment {$appointmentId} not added to OfficeAlly";
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
        return 'Payment wasn\'t added to appointment in OfficeAlly (OfficeAlly unavailable). Please try again later.';
    }
    
    public function getStatusCode(): int
    {
        return 409;
    }
}
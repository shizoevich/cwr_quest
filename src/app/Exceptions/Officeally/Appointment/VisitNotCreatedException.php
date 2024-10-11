<?php

namespace App\Exceptions\Officeally\Appointment;

use App\Exceptions\Officeally\OfficeallyException;
use Throwable;

class VisitNotCreatedException extends OfficeallyException
{
    
    public function __construct(int $appointmentId)
    {
        $message = "Visit for appointment {$appointmentId} not created.";
        parent::__construct($message, 0, null);
    }
    
    public function getHumanReadableMessage(): string
    {
        return 'Visit wasn\'t created.';
    }
    
    public function getStatusCode(): int
    {
        return 409;
    }
}
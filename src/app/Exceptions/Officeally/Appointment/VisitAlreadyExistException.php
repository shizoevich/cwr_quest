<?php

namespace App\Exceptions\Officeally\Appointment;

use App\Appointment;
use App\Exceptions\Officeally\OfficeallyException;
use Carbon\Carbon;

class VisitAlreadyExistException extends OfficeallyException
{
    private $appointment;
    
    /**
     * VisitAlreadyExistException constructor.
     *
     * @param Appointment $appointment
     * @param string      $message
     */
    public function __construct(Appointment $appointment, $message = "")
    {
        $this->appointment = $appointment;
        $dos = Carbon::createFromTimestamp($this->appointment->time)->toDateString();
        if(empty($message)) {
            $message = "Visit with DOS {$dos} already exist for patient {$this->appointment->patients_id}.";
        }
        parent::__construct($message, 0, null);
    }
    
    public function getHumanReadableMessage(): string
    {
        $dos = Carbon::createFromTimestamp($this->appointment->time)->format('m/d/Y');
        
        return "Visit with DOS {$dos} already exist for patient {$this->appointment->patients_id}.";
    }
    
    public function getStatusCode(): int
    {
        return 409;
    }
}
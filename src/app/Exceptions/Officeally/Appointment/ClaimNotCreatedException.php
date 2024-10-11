<?php

namespace App\Exceptions\Officeally\Appointment;

use App\Exceptions\Officeally\OfficeallyException;

class ClaimNotCreatedException extends OfficeallyException
{
    public function getHumanReadableMessage(): string
    {
        return $this->getMessage();
    }
    
    public function getStatusCode(): int
    {
        return 409;
    }
}
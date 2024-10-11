<?php

namespace App\Exceptions\Officeally\Appointment;

use App\Exceptions\Officeally\OfficeallyException;
use App\Provider;

class ClaimProviderNotUpdatedException extends OfficeallyException
{
    private $claimNumber;

    private $provider;

    public function __construct($claimNumber, Provider $provider)
    {
        $this->claimNumber = $claimNumber;
        $this->provider = $provider;

        $message = "Provider {$this->provider->provider_name} (ID: {$this->provider->officeally_id}) wasn't set into Claim {$this->claimNumber}.";
        parent::__construct($message, 0, null);
    }

    public function getHumanReadableMessage(): string
    {
        return "Provider {$this->provider->provider_name} (ID: {$this->provider->officeally_id}) wasn't set into Claim {$this->claimNumber}.";
    }
    
    public function getStatusCode(): int
    {
        return 409;
    }
}
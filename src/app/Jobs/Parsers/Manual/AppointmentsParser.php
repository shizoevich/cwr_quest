<?php

namespace App\Jobs\Parsers\Manual;

use App\Jobs\Parsers\Guzzle\OfficeRoomsParser;
use App\Jobs\Parsers\Guzzle\BillingProvidersParser;
use App\Jobs\Parsers\Guzzle\ProvidersParser;
use App\Jobs\Parsers\Guzzle\PatientsParser;
use App\Jobs\Parsers\Guzzle\AppointmentsParser as GuzzleAppointmentsParser;
use App\Jobs\UpdateProviderInsurances;

class AppointmentsParser extends AbstractParser
{
    protected function handleParser()
    {
        \Bus::dispatchNow(new OfficeRoomsParser());
        \Bus::dispatchNow(new BillingProvidersParser());
        \Bus::dispatchNow(new ProvidersParser());
        \Bus::dispatchNow(new PatientsParser());
        \Bus::dispatchNow(new GuzzleAppointmentsParser());
        \Bus::dispatchNow(new UpdateProviderInsurances());
    }
    
    protected function getParserName(): string
    {
        return 'appointments';
    }
}

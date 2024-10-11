<?php

namespace App\Jobs\Parsers\Manual;

use App\Jobs\Patients\CalculatePatientBalance;
use Carbon\Carbon;

class PaymentsParser extends AbstractParser
{
    protected function handleParser()
    {
        $dateFrom = Carbon::now()
            ->setTimezone('utc')
            ->startOfDay()
            ->subDays(2);
        $dateTo = Carbon::now()
            ->setTimezone('utc')
            ->startOfDay()
            ->addDays(1);
        
        \Bus::dispatchNow(new \App\Jobs\Parsers\Guzzle\PaymentsParser($dateFrom, $dateTo));
        \Bus::dispatchNow(new CalculatePatientBalance);
    }
    
    protected function getParserName(): string
    {
        return 'payments';
    }
}

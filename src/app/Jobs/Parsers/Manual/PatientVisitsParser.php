<?php

namespace App\Jobs\Parsers\Manual;

class PatientVisitsParser extends AbstractParser
{
    protected function handleParser()
    {
        \Bus::dispatchNow(new \App\Jobs\Parsers\Guzzle\PatientVisitsParser([
            'full-time' => false,
            'only-visits' => false,
            'month' => null,
            'date' => null,
        ]));
    }
    
    protected function getParserName(): string
    {
        return 'visits';
    }
}

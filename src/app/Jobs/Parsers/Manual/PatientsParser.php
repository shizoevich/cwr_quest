<?php

namespace App\Jobs\Parsers\Manual;

class PatientsParser extends AbstractParser
{
    protected function handleParser()
    {
        \Bus::dispatchNow(new \App\Jobs\Parsers\Guzzle\PatientsParser());
    }
    
    protected function getParserName(): string
    {
        return 'patients';
    }
}

<?php

namespace App\Jobs\Parsers\Manual;

class DiagnosesParser extends AbstractParser
{
    protected function handleParser()
    {
        \Bus::dispatchNow(new \App\Jobs\Parsers\Guzzle\DiagnosesParser());
    }
    
    protected function getParserName(): string
    {
        return 'diagnoses';
    }
}

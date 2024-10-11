<?php

namespace App\Jobs\Parsers\Manual;

class ProvidersParser extends AbstractParser
{
    protected function handleParser()
    {
        \Bus::dispatchNow(new \App\Jobs\Parsers\Guzzle\ProvidersParser());
    }
    
    protected function getParserName(): string
    {
        return 'providers';
    }
}

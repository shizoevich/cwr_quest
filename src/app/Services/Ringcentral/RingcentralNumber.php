<?php

namespace App\Services\Ringcentral;

class RingcentralNumber extends AbstractRingcentral
{
    public function list()
    {
        $response = $this->sdk->platform()->get('/account/~/extension/~/phone-number');
        
        return __data_get($response->jsonArray(), 'records', []);
    }
}
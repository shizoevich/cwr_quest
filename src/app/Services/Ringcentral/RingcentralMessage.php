<?php

namespace App\Services\Ringcentral;

class RingcentralMessage extends AbstractRingcentral
{
    public function list()
    {
        $response = $this->sdk->platform()->get('/account/~/extension/~/message-store/?messageType=Fax&direction=Outbound');
        
        return __data_get($response->jsonArray(), 'records', []);
    }
}
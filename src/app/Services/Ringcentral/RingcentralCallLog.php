<?php

namespace App\Services\Ringcentral;

class RingcentralCallLog extends AbstractRingcentral
{
    public function activeList()
    {
        $response = $this->sdk->platform()->get('/account/~/extension/~/active-calls');
        
        return __data_get($response->jsonArray(), 'records', []);
    }
    
    public function list($queryParameters = [])
    {
        $allRecords = [];
        $currentPage = 1;

        while (true) {
            $queryParameters = array_merge($queryParameters, ['page' => $currentPage]);
            $response = $this->sdk->platform()->get('/account/~/extension/~/call-log', $queryParameters);
            $responseJson = $response->jsonArray();
            $navigation = __data_get($responseJson , 'navigation', []);
            $records = __data_get($responseJson , 'records', []);
            $allRecords = array_merge($allRecords, $records);
            
            if (isset($navigation['nextPage'])) {
                $currentPage += 1;
            } else {
                break;
            }
        }
        
        return $allRecords;
    }
}

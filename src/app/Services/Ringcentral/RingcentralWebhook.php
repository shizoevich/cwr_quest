<?php

namespace App\Services\Ringcentral;

class RingcentralWebhook extends AbstractRingcentral
{
    public function list()
    {
        $response = $this->sdk->platform()->get('/subscription');
        
        return __data_get($response->jsonArray(), 'records', []);
    }
    
    public function get(string $webhookId)
    {
        $response = $this->sdk->platform()->get('/subscription/' . $webhookId);
        
        return $response->jsonArray();
    }
    
    public function store(array $eventFilters)
    {
        if (empty($eventFilters)) {
            return [];
        }
        
        $response = $this->sdk->platform()->post('/subscription', [
            'eventFilters' => $eventFilters,
            'deliveryMode'=> [
                'transportType'=> 'WebHook',
                'address'=> config('ringcentral.webhook_address'),
                'verificationToken' => config('ringcentral.webhook_verification_token')
            ]
        ]);
    
        return $response->jsonArray();
    }
    
    public function delete(string $webhookId)
    {
        $response = $this->sdk->platform()->delete('/subscription/' . $webhookId);
    
        return $response->ok();
    }
    
    public function renew(string $webhookId)
    {
        $response = $this->sdk->platform()->post('/subscription/' . $webhookId . '/renew');
        
        return $response->jsonArray();
    }
}
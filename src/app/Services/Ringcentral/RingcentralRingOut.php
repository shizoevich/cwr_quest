<?php

namespace App\Services\Ringcentral;

class RingcentralRingOut extends AbstractRingcentral
{
    public function get(string $ringOutId)
    {
        $response = $this->sdk->platform()->get('/account/~/extension/~/ring-out/' . $ringOutId);
        
        return $response->jsonArray();
    }
    
    public function store(string $from, string $to, bool $playPrompt = true)
    {
        $response = $this->sdk->platform()->post('/account/~/extension/~/ring-out', [
            'from' => ['phoneNumber' => $from],
            'to' => ['phoneNumber' => $to],
            'playPrompt' => $playPrompt
        ]);
    
        return $response->jsonArray();
    }
    
    public function delete(string $sessionId)
    {
        $response = $this->sdk->platform()->delete('/account/~/extension/~/ring-out/' . $sessionId);
        
        return $response->ok();
    }
}
<?php

namespace App\Services\Ringcentral;

use App\Option;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Services\Ringcentral\SDK\SDK;

abstract class AbstractRingcentral
{
    /** @var SDK */
    protected $sdk;
    
    public function __construct()
    {
        $this->initSdk();
        $this->login();
    }
    
    /**
     * @return bool
     * @throws \RingCentral\SDK\Http\ApiException
     */
    private function login()
    {
        $loginDetails = $this->getLoginDetails();
        $this->sdk->platform()->auth()->setData($loginDetails);
        if ($this->sdk->platform()->loggedIn()) {
            return true;
        }
        $this->sdk->platform()->login([
            'jwt' => config('ringcentral.jwt')
        ]);
        $this->updateLoginDetails($this->sdk->platform()->auth()->data());
        
        return $this->sdk->platform()->loggedIn();
    }
    
    private function initSdk()
    {
        if (!isset($this->sdk)) {
            $this->sdk = new SDK(
                config('ringcentral.appKey'),
                config('ringcentral.appSecret'),
                config('ringcentral.server')
            );
        }
    }
    
    private function updateLoginDetails(array $data)
    {
        $encryptedData = encrypt($data);
        Option::setOptionValue('ringcentral_credentials', $encryptedData);
    }
    
    /**
     * @return array
     */
    private function getLoginDetails()
    {
        $encryptedData = Option::getOptionValue('ringcentral_credentials');
        try {
            $data = decrypt($encryptedData);
        } catch (DecryptException $e) {
            \App\Helpers\SentryLogger::captureException($e);
            return [];
        }
        
        return $data ?? [];
    }
    
}
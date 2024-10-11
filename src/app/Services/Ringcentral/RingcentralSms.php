<?php

namespace App\Services\Ringcentral;

use App\Option;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Services\Ringcentral\SDK\SDK;

class RingcentralSms extends AbstractRingcentral
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
            'jwt' => config('ringcentral.sms.jwt')
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
        Option::setOptionValue('ringcentral_credentials_sms', $encryptedData);
    }

    /**
     * @return array
     */
    private function getLoginDetails()
    {
        $encryptedData = Option::getOptionValue('ringcentral_credentials_sms');
        try {
            $data = decrypt($encryptedData);
        } catch (DecryptException $e) {
            \App\Helpers\SentryLogger::captureException($e);
            return [];
        }

        return $data ?? [];
    }

    public function store(array $to, string $text)
    {
        $fromFormatted = ['phoneNumber' => config('ringcentral.sms_from')];
        $toFormatted = array_map(function($item) {
            return ['phoneNumber' => $item];
        }, $to);

        $response = $this->sdk->platform()->post('/account/~/extension/~/sms', [
            'from' => $fromFormatted,
            'to' => $toFormatted,
            'text' => $text
        ]);
    
        return $response->jsonArray();
    }
}
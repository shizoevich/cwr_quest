<?php

namespace App\Services\Ringcentral\SDK;

use RingCentral\SDK\SDK as BaseSDK;
use GuzzleHttp\Client as GuzzleClient;
use RingCentral\SDK\Http\Client;
use App\Services\Ringcentral\SDK\Platform\Platform;

class SDK extends BaseSDK
{
    /**
     * SDK constructor.
     *
     * @param string       $clientId
     * @param string       $clientSecret
     * @param string       $server
     * @param string       $appName
     * @param string       $appVersion
     * @param GuzzleClient $guzzle
     */
    public function __construct(
        $clientId,
        $clientSecret,
        $server,
        $appName = '',
        $appVersion = '',
        $guzzle = null
    ) {
        $pattern = "/[^a-z0-9-_.]/i";

        $appName = preg_replace($pattern, '', $appName);
        $appVersion = preg_replace($pattern, '', $appVersion);

        $this->_guzzle = $guzzle ? $guzzle : new GuzzleClient();

        $this->_client = new Client($this->_guzzle);

        $this->_platform = new Platform($this->_client, $clientId, $clientSecret, $server, $appName, $appVersion);
    }
}
<?php

namespace App\Services\Ringcentral\SDK\Platform;

use Exception;
use RingCentral\SDK\Http\ApiException;
use RingCentral\SDK\Http\ApiResponse;
use RingCentral\SDK\Platform\Platform as BasePlatform;

// @todo remove class if ringcentral/ringcentral-php will be updated to version >= 3.0
class Platform extends BasePlatform
{
    /**
     * @param string $username
     * @param string $extension
     * @param string $password
     * @return ApiResponse
     * @throws Exception    If it fails to retrieve/parse JSON data from he response.
     * @throws ApiException If there is an issue with the token request.
     */
    public function login($options)
    {
        if (is_string($options)) {
            $options = [
                'username'  => func_get_arg(0),
                'extension' => func_get_arg(1) ? func_get_arg(1) : null,
                'password'  => func_get_arg(2)
            ];
        }

        $response = !empty($options['code']) ? $this->requestToken(self::TOKEN_ENDPOINT, [

            'grant_type'        => 'authorization_code',
            'code'              => $options['code'],
            'redirect_uri'      => $options['redirectUri'],
            'access_token_ttl'  => self::ACCESS_TOKEN_TTL,
            'refresh_token_ttl' => self::REFRESH_TOKEN_TTL

        ]) : (!empty($options['jwt']) ? $this->requestToken(self::TOKEN_ENDPOINT, [

            'grant_type'        => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'         => $options['jwt'],
            'access_token_ttl'  => self::ACCESS_TOKEN_TTL,
            'refresh_token_ttl' => self::REFRESH_TOKEN_TTL

        ]) : $this->requestToken(self::TOKEN_ENDPOINT, [

            'grant_type'        => 'password',
            'username'          => $options['username'],
            'extension'         => $options['extension'] ? $options['extension'] : null,
            'password'          => $options["password"],
            'access_token_ttl'  => self::ACCESS_TOKEN_TTL,
            'refresh_token_ttl' => self::REFRESH_TOKEN_TTL

        ]));

        $this->_auth->setData($response->jsonArray());

        return $response;
    }
}

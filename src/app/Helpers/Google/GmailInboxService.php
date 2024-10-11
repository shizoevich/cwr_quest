<?php

namespace App\Helpers\Google;

use App\Option;
use Google_Client;
use Google_Service_Gmail;

class GmailInboxService
{
    public function getClient()
    {
        $client = new Google_Client();
        $client->setApplicationName(config('app.name'));
        $client->setAccessType("offline");
        $client->setScopes(Google_Service_Gmail::MAIL_GOOGLE_COM);
        $client->setAuthConfig(json_decode(Option::getOptionValue('gmail_api_credentials'), true));
        $accessToken = json_decode(Option::getOptionValue('gmail_api_token'), true);
        $client->setAccessToken($accessToken);
        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            }
        }
        return $client;

        // $client = new Google_Client();
        // $client->setApplicationName(config('app.name'));
        // $client->setAccessType("offline");    
        // $client->setScopes(Google_Service_Gmail::MAIL_GOOGLE_COM);
        // $client->setClientId(config('tridiuum_refresh_token.clientId'));
        // $client->setClientSecret(config('tridiuum_refresh_token.clientSecret'));
        // $client->refreshToken(config('tridiuum_refresh_token.clientRefreshToken'));
        // return $client;
    }
}

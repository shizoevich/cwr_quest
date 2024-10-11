<?php

namespace App\Services\Upheal;

use GuzzleHttp\Client;

class UphealService
{
    private static $client;

    private static function getClient()
    {
        if (!isset(self::$client)) {
            self::$client = new Client([
                'headers'=> [
                    'Authorization' => config('upheal.access_key'),
                ],
                'base_uri' => config('upheal.api_url'),
            ]);
        }

        return self::$client;
    }

    private static function jsonDecode($response)
    {
        return \GuzzleHttp\json_decode((string) $response->getBody(), true);
    }

    public static function createPatient($uphealTherapistId, $firstName, $lastName, $email)
    {
        $client = self::getClient();
        $data = [
            'therapistUserId' => $uphealTherapistId,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'locale' => 'en-US'
        ];
        
        $response = $client->request('POST', 'users/clients', [
            'json' => $data
        ]);

        return self::jsonDecode($response);
    }

    public static function createProvider($firstName, $lastName, $email)
    {
        $client = self::getClient();
        $data = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'locale' => 'en-US'
        ];
        
        $response = $client->request('POST', 'users/providers', [
            'json' => $data
        ]);

        return self::jsonDecode($response);
    }
}

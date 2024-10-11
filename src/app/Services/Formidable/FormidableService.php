<?php

namespace App\Services\Formidable;

use GuzzleHttp\Client;

class FormidableService
{
    private static $client;

    private static function getClient()
    {
        if (!isset(self::$client)) {
            self::$client = new Client([
                'headers'=> [
                    'Authorization' => 'Basic ' . base64_encode(config('satisfaction_survey.api_key') . ':x'),
                ],
                'base_uri' => config('satisfaction_survey.api_url'),
            ]);
        }

        return self::$client;
    }

    public static function getFormFields($formId)
    {
        $client = self::getClient();
        $response =  $client->request('GET', 'forms/' . $formId . '/fields');

        return $response->getBody()->getContents();
    }

    public static function getFormEntries($formId, $pageSize = 25, $order = 'ASC')
    {
        $client = self::getClient();
        $response =  $client->request('GET', 'forms/' . $formId . '/entries?page_size=' . $pageSize . '&order=' . $order);

        return $response->getBody()->getContents();
    }
}

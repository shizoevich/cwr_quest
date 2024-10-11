<?php

namespace Tests\Helpers\OfficeAlly;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\DomCrawler\Crawler;

class LoginOfficeAllyHelper
{
    public static function getRequestVerificationTokenFromHtml($html)
    {
        $crawler = new Crawler($html);

        return $crawler->filter('[name="__RequestVerificationToken"]')->first()->attr('value');
    }

    public static function getClient($cookiesData)
    {
        $cookies = CookieJar::fromArray($cookiesData, 'pm.officeally.com');
        $clientConfig = [
            'cookies'         => $cookies ?? true,
            'verify'          => false,
            'base_uri'        => 'https://pm.officeally.com/pm/',
            'allow_redirects' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36'
            ]
        ];

        if (config('officeally.proxy_enabled')) {
            $clientConfig['proxy'] = config('officeally.proxy');
        }
        
        return new Client($clientConfig);
    }

    public static function isResponseAuthenticated($response): bool
    {
        $locationHeader = data_get($response->getHeader('Location'), '0');
        if(!$locationHeader) {
            return true;
        }
        
        return !str_contains($locationHeader, 'Login.aspx');
    }
}
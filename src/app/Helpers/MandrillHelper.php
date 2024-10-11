<?php

namespace App\Helpers;

use MailchimpTransactional\ApiClient;

class MandrillHelper
{
    private static $client;

    private static function getClient()
    {
        if (self::$client) {
            return self::$client;
        }
        
        $config = config('services.mandrill');
        self::$client = new ApiClient();
        self::$client->setApiKey($config['secret']);

        return self::$client;
    }

    public static function checkEmailInRejectList(string $email)
    {
        return !!self::findEmailInRejectList($email);
    }

    public static function findEmailInRejectList(string $email)
    {
        $reject = null;

        $rejectList = self::getRejectList();
        $rejectList = array_filter($rejectList, function ($reject) use ($email) {
            return $reject->email === $email;
        });

        if (!empty($rejectList)) {
            $reject = array_pop($rejectList);
        }

        return $reject;
    }

    private static function getRejectList()
    {
        return self::getClient()->rejects->list();
    }

    public static function removeEmailFromRejectList(string $email)
    {
        $body = [
            'email' => $email,
        ];

        $response = self::getClient()->rejects->delete($body);

        return $response->deleted;
    }

    public static function addEmailToRejectList(string $email)
    {
        $body = [
            'email' => $email,
        ];

        $response = self::getClient()->rejects->add($body);

        return $response->added;
    }
}

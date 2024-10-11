<?php

namespace App\MonologHandlers;

use GuzzleHttp\MessageFormatter;
use function GuzzleHttp\Psr7\str;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MysqlMessageFormatter extends MessageFormatter
{
    public function format(
        RequestInterface $request,
        ResponseInterface $response = null,
        \Exception $error = null
    ) {
        try {
            $bodyString = (string)$request->getBody();
            $requestBody = json_decode($bodyString, true);
            if($requestBody === null) {
                parse_str($bodyString, $requestBody);
            }
            $requestBody = mask_password_field($requestBody);

            return json_encode([
                'url'          => (string) $request->getUri(),
                'method'       => $request->getMethod(),
                'status_code'  => optional($response)->getStatusCode(),
                'request_body' => json_encode($requestBody),
            ]);
        } catch(\Exception $e) {
            \Log::error($e->getMessage());
            \App\Helpers\SentryLogger::captureException($e);
        }

        return null;
    }
}
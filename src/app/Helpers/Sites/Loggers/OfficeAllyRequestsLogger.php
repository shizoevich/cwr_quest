<?php

namespace App\Helpers\Sites\Loggers;

use Monolog\Logger;
use Monolog\Handler\SlackWebhookHandler;
use Sentry\ClientBuilder;
use Sentry\Monolog\Handler;
use Sentry\State\Hub;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;
use Psr\Log\LogLevel;

class OfficeAllyRequestsLogger
{
    /**
     * @var Logger
     */
    protected $slackLogger;

    /**
     * @var Logger
     */
    protected $sentryLogger;

    /**
     * @return HandlerStack
     */
    public function createLoggingHandlerStack()
    {
        $stack = HandlerStack::create();

        if(config('services.slack.webhook_url')) {
            $stack->unshift(
                $this->createSlackLoggingMiddleware()
            );
        }

        if(config('officeally.sentry_dsn')) {
            $stack->unshift(
                $this->createSentryLoggingMiddleware()
            );
        }

        return $stack;
    }

    /**
     * @return callable
     */
    private function createSlackLoggingMiddleware()
    {
        $messageTemplate = '[OFFICE_ALLY] ' . MessageFormatter::CLF;
        return Middleware::log(
            $this->getSlackLogger(),
            new MessageFormatter($messageTemplate),
            LogLevel::DEBUG
        );
    }

    /**
     * @return Logger
     */
    private function getSlackLogger()
    {
        if(!$this->slackLogger) {
            $handler = new SlackWebhookHandler(config('services.slack.webhook_url'), null, null, true, null, false, false, config('officeally.slack_log_level'));
            $this->slackLogger = with(new Logger('OFFICE_ALLY'))->pushHandler($handler);
        }

        return $this->slackLogger;
    }

    /**
     * @return callable
     */
    private function createSentryLoggingMiddleware()
    {
        $messageTemplate = '[OFFICE_ALLY:'. config('app.aws_instance_ip') .'] "STATUS:{code}; METHOD:{method}; TARGET:{target}" ({ts})' . "\n>>>>>>>>\nREQUEST_BODY:\n{req_body}" . "\n>>>>>>>>\nERROR_MESSAGE:\n{error}";
        return Middleware::log(
            $this->getSentryLogger(),
            new MessageFormatter($messageTemplate),
            LogLevel::DEBUG
        );
    }

    /**
     * @return Logger
     */
    private function getSentryLogger(): Logger
    {
        if (!$this->sentryLogger) {
            $client = ClientBuilder::create([
                'dsn' => config('officeally.sentry_dsn')
            ])->getClient();
            $handler = new Handler(
                new Hub($client),
                config('officeally.sentry_log_level')
            );
            $this->sentryLogger = with(new Logger('OFFICE_ALLY'))->pushHandler($handler);
        }

        return $this->sentryLogger;
    }
}
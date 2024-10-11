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

class TridiuumRequestsLogger
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

        if(config('tridiuum.sentry_dsn')) {
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
        $messageTemplate = '[TRIDIUUM] ' . MessageFormatter::CLF;
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
            $handler = new SlackWebhookHandler(config('services.slack.webhook_url'), null, null, true, null, false, false, config('tridiuum.slack_log_level'));
            $this->slackLogger = with(new Logger('TRIDIUUM'))->pushHandler($handler);
        }

        return $this->slackLogger;
    }

    /**
     * @return callable
     */
    private function createSentryLoggingMiddleware()
    {
        $messageTemplate = '[TRIDIUUM:'. config('app.aws_instance_ip') .'] "STATUS:{code}; METHOD:{method}; TARGET:{target}" ({ts})' . "\n>>>>>>>>\nREQUEST_BODY:\n{req_body}" . "\n>>>>>>>>\nERROR_MESSAGE:\n{error}";
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
                'dsn' => config('tridiuum.sentry_dsn')
            ])->getClient();
            $handler = new Handler(
                new Hub($client),
                config('tridiuum.sentry_log_level')
            );
            $this->sentryLogger = with(new Logger('TRIDIUUM'))->pushHandler($handler);
        }

        return $this->sentryLogger;
    }
}
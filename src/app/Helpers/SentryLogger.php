<?php

namespace App\Helpers;

use Sentry\ClientBuilder;
use Sentry\State\Hub;
use Sentry\State\Scope;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionContext;
use Sentry\Tracing\SpanStatus;
use Sentry;

class SentryLogger
{
    private static $officeAllySentryClient;

    private static $tridiuumSentryClient;

    private static function getOfficeAllySentryClient()
    {
        if (!isset(self::$officeAllySentryClient)) {
            $client = ClientBuilder::create([
                'dsn' => config('officeally.sentry_dsn')
            ])->getClient();
            self::$officeAllySentryClient = new Hub($client);
        }

        return self::$officeAllySentryClient;
    }

    private static function getTridiuumSentryClient()
    {
        if (!isset(self::$tridiuumSentryClient)) {
            $client = ClientBuilder::create([
                'dsn' => config('tridiuum.sentry_dsn')
            ])->getClient();
            self::$tridiuumSentryClient = new Hub($client);
        }

        return self::$tridiuumSentryClient;
    }

    public static function officeAllyCaptureException($e, $tags = [])
    {
        $client = self::getOfficeAllySentryClient();

        if (!count($tags)) {
            $client->captureException($e);
            return;
        }

        $client->withScope(function (Scope $scope) use (&$client, $e, $tags): void {
            foreach ($tags as $key => $value) {
                $scope->setTag($key, $value);
            }

            $client->captureException($e);
        });
    }

    public static function officeAllyCaptureMessage($message, $tags = [])
    {
        $client = self::getOfficeAllySentryClient();
        
        if (!count($tags)) {
            $client->captureMessage('[OFFICE_ALLY:'. config('app.aws_instance_ip') .'] ' . $message);
            return;
        }

        $client->withScope(function (Scope $scope) use (&$client, $message, $tags): void {
            foreach ($tags as $key => $value) {
                $scope->setTag($key, $value);
            }

            $client->captureMessage('[OFFICE_ALLY:'. config('app.aws_instance_ip') .'] ' . $message);
        });
    }

    public static function tridiuumCaptureException($e, $tags = [])
    {
        $client = self::getTridiuumSentryClient();
        
        if (!count($tags)) {
            $client->captureException($e);
            return;
        }

        $client->withScope(function (Scope $scope) use (&$client, $e, $tags): void {
            foreach ($tags as $key => $value) {
                $scope->setTag($key, $value);
            }

            $client->captureException($e);
        });
    }

    public static function tridiuumCaptureMessage($message, $tags = [])
    {
        $client = self::getTridiuumSentryClient();

        if (!count($tags)) {
            $client->captureMessage('[TRIDIUUM:'. config('app.aws_instance_ip') .'] ' . $message);
            return;
        }

        $client->withScope(function (Scope $scope) use (&$client, $message, $tags): void {
            foreach ($tags as $key => $value) {
                $scope->setTag($key, $value);
            }

            $client->captureMessage('[TRIDIUUM:'. config('app.aws_instance_ip') .'] ' . $message);
        });
    }

    public static function captureException($e)
    {
        if (app()->bound('sentry')) {
            app('sentry')->captureException($e);
        }
    }

    public static function captureMessage($message)
    {
        if (app()->bound('sentry')) {
            app('sentry')->captureMessage($message);
        }
    }

    public static function startOfficeAllyTransaction(string $transactionName, string $shortCode, array $tags = []): Transaction
    {
        $client = self::getOfficeAllySentryClient();

        $tags['transaction.service'] = 'office-ally';

        return self::startTransaction($client, $transactionName, $shortCode, $tags);
    }

    public static function startTridiuumTransaction(string $transactionName, string $shortCode, array $tags = []): Transaction
    {
        $client = self::getTridiuumSentryClient();

        $tags['transaction.service'] = 'tridiuum';

        return self::startTransaction($client, $transactionName, $shortCode, $tags);
    }

    protected static function startTransaction(Hub $client, string $transactionName, string $shortCode, array $tags = []): Transaction
    {
        $transactionContext = new TransactionContext();
        $transactionContext->setName($transactionName);
        $transactionContext->setOp($shortCode);

        // if use $client instead of Sentry sql queries will be missing from trace 
        $transaction = Sentry::startTransaction($transactionContext);
        if (count($tags)) {
            $transaction->setTags($tags);
        }
        $transaction->setSampled(true);
        $client->setSpan($transaction);

        // without bindClient and setSpan to Sentry the trace won't have all exceptions
        Sentry::bindClient($client->getClient());
        Sentry::setSpan($transaction);

        return $transaction;
    }

    public static function finishTransaction(Transaction $transaction): void
    {
        $transaction->setTags([
            'transaction.status' => 'ok',
        ]);
        $transaction->setStatus(SpanStatus::ok());

        $transaction->finish();
    }

    public static function failTransaction(Transaction $transaction): void
    {
        $transaction->setTags([
            'transaction.status' => 'internal_error',
        ]);
        $transaction->setStatus(SpanStatus::internalError());

        $transaction->finish();
    }
}

<?php

namespace App\Jobs\Tridiuum;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Helpers\SentryLogger;
use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;

/**
 * Class AbstractParser
 * @package App\Jobs\Tridiuum
 */
abstract class AbstractParser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $transaction;
    
    /**
     * AbstractParser constructor.
     */
    public function __construct()
    {
        //
    }
    
    /**
     * @throws \Throwable
     */
    public function handle()
    {
        try {
            $this->transaction = SentryLogger::startTridiuumTransaction(
                static::class, 
                'parser',
                [
                    'transaction.type' => 'parser',
                ]
            );

            $this->handleParser();

            SentryLogger::finishTransaction($this->transaction);
        } catch(\Throwable $e) {
            $this->notifyOnError($e);
            throw $e;
        }
    }

    protected function notifyOnError(\Throwable $e) 
    {
        if ($this->transaction) {
            SentryLogger::failTransaction($this->transaction);
        }

        with(new ExceptionNotificator())->tridiuumNotifyAndSendToSentry(new AnErrorOccurred($e));
    }
    
    abstract protected function handleParser();
}

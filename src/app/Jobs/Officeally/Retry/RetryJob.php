<?php

namespace App\Jobs\Officeally\Retry;

use App\Option;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RetryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var string */
    public $defaultOfficeAllyAccount;

    /** @var string */
    public $officeAllyAccount;

    /** @var int */
    public $tries = 8;

    /** @var array */
    public $officeAllyAccounts = [Option::OA_ACCOUNT_1, Option::OA_ACCOUNT_2, Option::OA_ACCOUNT_3];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($officeAllyAccount)
    {
        $this->tries = count(config('parser.job_retry_backoff_intervals'));
        $this->connection = 'database';
        $this->queue = 'oa-retry-parser';

        $this->defaultOfficeAllyAccount = $officeAllyAccount;
        $this->officeAllyAccount = $officeAllyAccount;

        $this->filterOfficeAllyAccounts($officeAllyAccount);
    }

    private function filterOfficeAllyAccounts($excludedAccount)
    {
        $this->officeAllyAccounts = array_filter($this->officeAllyAccounts, function ($value) use ($excludedAccount) {
            return $value !== $excludedAccount;
        });
    }

    /**
     * Handle retry logic for authentication exceptions.
     *
     * @return void
     */
    protected function handleRetry()
    {
        if (count($this->officeAllyAccounts)) {
            \Cache::forget('office_ally_authentication_failed');
            $account = array_pop($this->officeAllyAccounts);
            $this->officeAllyAccount = $account;
            return \Bus::dispatchNow($this);
        }

        $this->resetOfficeAllyAccounts();
        $releaseTime = $this->getReleaseTime();

        if ($this->job) {
            $this->release($releaseTime);
        } else {
            dispatch($this->delay($releaseTime));
        }
    }

    private function resetOfficeAllyAccounts()
    {
        $this->officeAllyAccounts = [Option::OA_ACCOUNT_1, Option::OA_ACCOUNT_2, Option::OA_ACCOUNT_3];
        $this->filterOfficeAllyAccounts($this->defaultOfficeAllyAccount);
        $this->officeAllyAccount = $this->defaultOfficeAllyAccount;
    }

    private function getReleaseTime()
    {
        $currentAttempt = $this->attempts();
        $releaseTimes = config('parser.job_retry_backoff_intervals');

        return $releaseTimes[$currentAttempt - 1];
    }
}

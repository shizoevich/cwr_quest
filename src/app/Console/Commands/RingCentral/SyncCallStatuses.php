<?php

namespace App\Console\Commands\RingCentral;

use Illuminate\Console\Command;

class SyncCallStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ringcentral:sync-call-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Ring Central call statuses';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \Bus::dispatchNow(new \App\Jobs\RingCentral\SyncCallStatuses());
    }
}

<?php

namespace App\Console\Commands\Tridiuum;

use Illuminate\Console\Command;
use App\Jobs\Tridiuum\SyncAvailabilities as SyncAvailabilitiesJob;

class SyncAvailabilities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tridiuum:sync-availabilities {provider_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync availabilities with Tridiuum';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \Bus::dispatchNow(new SyncAvailabilitiesJob($this->argument('provider_id')));
    }
}

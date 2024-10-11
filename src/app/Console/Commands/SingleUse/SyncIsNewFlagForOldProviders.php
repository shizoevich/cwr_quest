<?php

namespace App\Console\Commands\SingleUse;

use Illuminate\Console\Command;
use App\Provider;

class SyncIsNewFlagForOldProviders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:is-new-flag-for-old-providers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync old providers to set they column is_new to false';

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
        Provider::withTrashed()->update(['is_new' => false]);

        $this->info('All old providers have been updated successfully.');
    }
}

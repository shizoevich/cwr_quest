<?php

namespace App\Console\Commands\RingCentral;

use Illuminate\Console\Command;

class SyncCallFaxes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ringcentral:faxes {page=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Ring Central call faxes';

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
        \Bus::dispatchNow(new \App\Jobs\RingCentral\SyncCallFaxes($this->argument('page')));
    }
}

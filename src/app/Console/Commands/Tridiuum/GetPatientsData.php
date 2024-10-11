<?php

namespace App\Console\Commands\Tridiuum;

use Illuminate\Console\Command;
use App\Jobs\Tridiuum\GetPatients;
class GetPatientsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tridiuum:get-patients';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse patients from Tridiuum';

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
        $job = (new GetPatients(true))->onQueue('tridiuum');
        dispatch($job);
    }
}

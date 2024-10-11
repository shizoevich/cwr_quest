<?php

namespace App\Console\Commands;

use App\Jobs\Supervisors\SyncAttachedPatientsToSupervisor as SyncAttachedPatientsToSupervisorJob;
use App\Models\Provider\ProviderSupervisor;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncAttachedPatientsToSupervisor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:attached-patients-to-supervisor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        ProviderSupervisor::query()
            ->whereDate('attached_at', '=', Carbon::today()->toDateString())
            ->orWhereDate('detached_at', '=', Carbon::today()->toDateString())
            ->each(function ($providerSupervisor) {
                dispatch(new SyncAttachedPatientsToSupervisorJob($providerSupervisor));
            });
    }
}

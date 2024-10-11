<?php

namespace App\Console\Commands\SingleUse;

use App\Models\PatientHasProvider;
use App\Models\Provider\ProviderSupervisor;
use Illuminate\Console\Command;

class UpdateSuperviseeIdInPatientsHasProvidersTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:supervisee-id-in-patients-has-providers-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update supervisee_id column in patients_has_providers table';

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
            ->whereNull('detached_at')
            ->each(function ($providerSupervisor) {
                $providerSupervisor->provider->patients()->each(function ($patient) use ($providerSupervisor) {
                    PatientHasProvider::query()
                        ->where([
                            'patients_id' => $patient->id,
                            'providers_id' => $providerSupervisor->supervisor_id,
                            'chart_read_only' => true
                        ])
                        ->whereNull('supervisee_id')
                        ->update(['supervisee_id' => $providerSupervisor->provider_id]);
                });
            });
    }
}

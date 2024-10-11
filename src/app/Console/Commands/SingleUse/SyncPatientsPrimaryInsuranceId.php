<?php

namespace App\Console\Commands\SingleUse;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use App\Patient;
use App\PatientInsurance;

class SyncPatientsPrimaryInsuranceId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:patients-primary-insurance-id';

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
        Patient::query()
            ->select([
                'id',
                'primary_insurance',
            ])
            ->whereNotNull('primary_insurance')
            ->whereNull('primary_insurance_id')
            ->chunkById(100, function(Collection $patients) {
                $patients->each(function(Patient $patient) {
                    $insurance = PatientInsurance::where('insurance', $patient->primary_insurance)->first();
                    if (isset($insurance)) {
                        $patient->primary_insurance_id = $insurance->id;
                        $patient->save();
                    }
                });
            });
    }
}

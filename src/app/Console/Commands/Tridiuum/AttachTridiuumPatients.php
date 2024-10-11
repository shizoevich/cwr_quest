<?php

namespace App\Console\Commands\Tridiuum;

use App\Jobs\Tridiuum\SyncPatientEmail;
use App\Models\TridiuumPatient;
use App\Patient;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class AttachTridiuumPatients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tridiuum:attach-patients';

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
        TridiuumPatient::query()->whereNull('internal_id')->chunkById('1000', function (Collection $tridiuumPatients) {
            $tridiuumPatients->each(function (TridiuumPatient $tridiuumPatient) {
                $fullname = explode(' ', $tridiuumPatient->first_name);
                $tridiuumPatientFirstName = $fullname[0];
                $patient = Patient::query()
                    ->where('first_name', $tridiuumPatientFirstName)
                    ->where('last_name', $tridiuumPatient->last_name)
                    ->where('date_of_birth', $tridiuumPatient->date_of_birth)
                    ->first();
                if ($patient) {
                    $tridiuumPatient->update(['internal_id' => $patient->getKey()]);
                }
            });
        });
        dispatch(new SyncPatientEmail());
    }
}

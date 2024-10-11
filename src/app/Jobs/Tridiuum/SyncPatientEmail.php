<?php

namespace App\Jobs\Tridiuum;

use App\Models\TridiuumPatient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class SyncPatientEmail implements ShouldQueue
{

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        TridiuumPatient::query()
            ->select([
                'tridiuum_patients.email',
                'tridiuum_patients.internal_id'
            ])
            ->whereNotNull('tridiuum_patients.email')
            ->whereNotNull('tridiuum_patients.internal_id')
            ->join('patients', 'patients.id', '=', 'tridiuum_patients.internal_id')
            ->whereNull('patients.email')
            ->chunkById(100, function(Collection $patients) {
                $patients->each(function(TridiuumPatient $patient) {
                    $patient->patient()->update(['email' => $patient->email]);
                });
            }, 'tridiuum_patients.id', 'id');
    }
}

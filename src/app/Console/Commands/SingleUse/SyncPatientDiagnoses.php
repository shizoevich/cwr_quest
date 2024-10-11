<?php

namespace App\Console\Commands\SingleUse;

use App\Models\Diagnose;
use App\Models\Patient\PatientDiagnose;
use App\PatientDiagnoseOld;
use Illuminate\Console\Command;

class SyncPatientDiagnoses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diagnoses:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        PatientDiagnoseOld::query()
            ->select([
                'patient_diagnoses_old.*',
                'patients.id AS patient_id'
            ])
            ->join('patients', 'patients.patient_id', 'patient_diagnoses_old.patient_officeally_id')
            ->each(function(PatientDiagnoseOld $oldDiagnoseRecord) {
                $diagnoses = explode('","', $oldDiagnoseRecord->diagnose);
                foreach ($diagnoses as $diagnose) {
                    $diagnose = trim($diagnose, ' "');
                    $matches = [];
                    preg_match('/^(?<code>[\w\.]+) - (?<description>.+)$/', $diagnose, $matches);
                    if(isset($matches['code']) && isset($matches['description'])) {
                        $matches['code'] = str_replace('.', '', $matches['code']);
                        $diagnose = Diagnose::query()->firstOrCreate(['code' => $matches['code']], ['description' => $matches['description']]);
                        PatientDiagnose::query()->firstOrCreate([
                            'patient_id' => $oldDiagnoseRecord->patient_id,
                            'diagnose_id' => $diagnose->getKey(),
                        ]);
                    } else {
                        $this->warn($oldDiagnoseRecord->patient_officeally_id . ' ' . $diagnose);
                    }
                }
            });
    }
}

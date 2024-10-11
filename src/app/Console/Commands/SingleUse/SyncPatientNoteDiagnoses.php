<?php

namespace App\Console\Commands\SingleUse;

use App\Models\Diagnose;
use App\Models\Patient\PatientDiagnose;
use App\PatientDiagnoseOld;
use App\PatientNote;
use App\PatientNoteDiagnoses;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SyncPatientNoteDiagnoses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patient-note-diagnoses:sync';

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
        PatientNote::query()
            ->withTrashed()
            ->whereNotNull('diagnosis_icd_code')
            ->chunkById(1000, function(Collection $patientNotes) {
                $patientNotes->each(function (PatientNote $patientNote) {
                    $diagnoses = explode('","', $patientNote->diagnosis_icd_code);
                    foreach ($diagnoses as $diagnose) {
                        $parsedDiagnose = parse_diagnose($diagnose, true, true);
                        if($parsedDiagnose && $patientNote->id && $parsedDiagnose->id) {
                            $patientNoteDiagnoses = PatientNoteDiagnoses::where(['patient_note_id' => $patientNote->getKey(), 'diagnose_id' => $parsedDiagnose->getKey()])->first();

                            if (!$patientNoteDiagnoses) {
                                PatientNoteDiagnoses::create(['patient_note_id' => $patientNote->getKey(), 'diagnose_id' => $parsedDiagnose->getKey()]);
                            }
                            
//                            $patientNote->diagnoses()->attach($parsedDiagnose->getKey());
                        } else {
                            $this->warn($patientNote->getKey() . ' ' . $diagnose);
                        }
                    }
                });
            });
    }
}

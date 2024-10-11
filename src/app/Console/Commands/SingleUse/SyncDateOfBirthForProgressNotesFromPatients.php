<?php

namespace App\Console\Commands\SingleUse;

use App\PatientNote;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SyncDateOfBirthForProgressNotesFromPatients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:patient-note-date-of-birth';

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
        PatientNote::query()
            ->select(['patient_notes.id', 'patient_notes.patients_id', 'patient_notes.date_of_birth'])
            ->whereNull('patient_notes.date_of_birth')
            ->whereNotNull('patient_notes.patients_id')
            ->orderBy('patient_notes.id', 'desc')
            ->chunkById(1000, function (Collection $patientNotes) {
                $patientNotes->each(function ($patientNote) {
                    $strToDump = 'Patient note id: ' . $patientNote->id . ';' . ' date_of_birth: ' . $patientNote->patient->date_of_birth;
                    dump($strToDump);

                    PatientNote::where('id', $patientNote->id)->update([
                        'date_of_birth' => $patientNote->patient->date_of_birth,
                    ]);
                });

            }, 'patient_notes.id', 'id');
    }
}

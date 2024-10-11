<?php

namespace App\Console\Commands\SingleUse;

use Illuminate\Console\Command;
use App\PatientNote;
use App\PatientInsurance;
use App\Http\Controllers\Utils\PdfUtils;
use App\Jobs\PatientNotes\ResavePatientNoteDocument;
use App\Traits\GoogleDrive\CopyPatientNoteAndAssessmentService;

class ResavePatientNoteDocumentsForTriWest extends Command
{
    use PdfUtils, CopyPatientNoteAndAssessmentService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resave-documents:patient-notes-for-tri-west';

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
        $insurance = PatientInsurance::where('insurance', 'PGBA TriWest VA CCN')->first();
        if (empty($insurance)) {
            return;
        }

        PatientNote::query()
            ->select(['patient_notes.*'])
            ->join('patients', 'patients.id', '=', 'patient_notes.patients_id')
            ->where('patient_notes.note_version', '=', 1.0)
            ->where('patient_notes.is_finalized', '=', true)
            ->where('patients.primary_insurance_id', '=', $insurance->id)
            ->whereDate('patient_notes.finalized_at', '<', '2023-09-23')
            ->whereNotNull('patient_notes.patients_id')
            ->chunkById(100, function ($notes) {
                foreach ($notes as $note) {
                    $strToDump = 'Patient note id: ' . $note->id . ';';
                    dump($strToDump);

                    \Bus::dispatchNow(new ResavePatientNoteDocument($note));

                    $note->update(['note_version' => 2.0]);
                }
            }, 'patient_notes.id', 'id');
    }
}

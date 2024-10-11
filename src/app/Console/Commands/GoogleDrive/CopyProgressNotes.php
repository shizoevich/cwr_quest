<?php

namespace App\Console\Commands\GoogleDrive;

use App\PatientNote;
use App\Traits\GoogleDrive\CopyPatientNoteAndAssessmentService;
use Illuminate\Console\Command;

class CopyProgressNotes extends Command
{
    use CopyPatientNoteAndAssessmentService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copy:patient_progress_note';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'copy patient progress note from s3 to google drive';

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
            ->select('id', 'patients_id', 'google_drive', 'date_of_service', 'is_finalized')
            ->where('google_drive', '=', false)
            ->where('is_finalized', '=', true)
            ->whereNull('deleted_at')
            ->whereNotNull('patients_id')
            ->whereNotNull('date_of_service')
            ->chunk(100, function ($patientDocuments) {
                foreach ($patientDocuments as $patientDocument) {
                    $patientDocumentDocumentTypeId = "888";
                    $this->makeCopyPatientNoteAndAssessment(
                        $patientDocument->id,
                        $patientDocumentDocumentTypeId,
                        $patientDocument->patients_id,
                        $patientDocument->date_of_service,
                        'progress_notes'
                    );
                    PatientNote::where('id', $patientDocument->id)
                        ->each(function ($patientNote) {
                            $patientNote->update(['google_drive' => true]);
                        });
                }
            });
    }
}

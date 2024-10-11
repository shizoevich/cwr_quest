<?php

namespace App\Console\Commands\GoogleDrive;

use App\PatientAssessmentForm;
use App\Traits\GoogleDrive\CopyPatientNoteAndAssessmentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CopyPatientAssessmentForms extends Command
{
    use CopyPatientNoteAndAssessmentService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copy:patient_assessment_form';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'copy patient assessment form from s3 to google drive';

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
        DB::table('patient_electronic_documents')
            ->select('id', 'patient_id', 'document_type_id', 'google_drive', 'created_at')
            ->where('google_drive', '=', false)
            ->whereNull('deleted_at')
            ->whereNotNull('document_type_id')
            ->whereNotNull('patient_id')
            ->whereNotNull('created_at')
            ->orderBy('id')
            ->chunk(100, function ($patientDocuments) {
                foreach ($patientDocuments as $patientDocument) {
                    $this->makeCopyPatientNoteAndAssessment(
                        $patientDocument->id,
                        $patientDocument->document_type_id,
                        $patientDocument->patient_id,
                        $patientDocument->created_at,
                        'patient_assessment_forms'
                    );
                    DB::table('patient_electronic_documents')
                        ->where('id', $patientDocument->id)
                        ->update(['google_drive' => true]);
                }
            });
    }
}

<?php

namespace App\Console\Commands\GoogleDrive;

use App\PatientDocument;
use Illuminate\Console\Command;
use App\Traits\GoogleDrive\CopyPatientDocumentService;

class CopyPatientDocuments extends Command
{
    use CopyPatientDocumentService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copy:patient_docs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'copy patient docs from s3 to google drive';

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
        PatientDocument::query()
            ->select('id', 'aws_document_name', 'original_document_name', 'patient_id', 'google_drive', 'document_type_id', 'created_at')
            ->where('google_drive', '=', false)
            ->whereNull('deleted_at')
            ->whereNotNull('original_document_name')
            ->whereNotNull('aws_document_name')
            ->whereNotNull('document_type_id')
            ->whereNotNull('patient_id')
            ->whereNotNull('created_at')
            ->orderBy('id')
            ->chunk(100, function ($patientDocuments) {
                foreach ($patientDocuments as $patientDocument) {
                    $this->makeCopyPatientDocument(
                        $patientDocument->original_document_name,
                        $patientDocument->document_type_id,
                        $patientDocument->patient_id,
                        $patientDocument->created_at,
                        $patientDocument->aws_document_name
                    );
                    PatientDocument::where('id', $patientDocument->id)
                        ->each(function (PatientDocument $patientDocument) {
                            $patientDocument->update(['google_drive' => true]);
                        });
                }
            });
    }
}

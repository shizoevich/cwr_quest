<?php

namespace App\Console\Commands\Tridiuum;

use App\Models\TridiuumPatientDocument;
use App\PatientDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class DeleteMissingDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tridiuum:delete-missing-documents';

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
     */
    public function handle()
    {
        $baseQuery = TridiuumPatientDocument::query()
            ->select([
                'tridiuum_patient_documents.*',
                'patient_documents.aws_document_name',
            ])
            ->join('patient_documents', 'patient_documents.id', '=', 'tridiuum_patient_documents.internal_id');
        $count = $baseQuery->count();
        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();
        $deletedCount = 0;
        $baseQuery->chunkById(1000, function(Collection $documents) use (&$progressBar, &$deletedCount) {
            $documents->each(function(TridiuumPatientDocument $document) use (&$progressBar, &$deletedCount) {
                $doc = \Storage::disk('patients_docs')->get($document->aws_document_name);
                if($doc === 'No Report Found') {
                    $this->deleteDocument($document);
                    $deletedCount++;
                }
                $progressBar->advance();
            });
        }, 'tridiuum_patient_documents.id', 'id');
        $progressBar->finish();
        $this->line('Deleted Documents: ' . $deletedCount);
    }

    /**
     * @param TridiuumPatientDocument $document
     *
     * @throws \Exception
     */
    private function deleteDocument(TridiuumPatientDocument $document)
    {
        \Storage::disk('patients_docs')->delete($document->aws_document_name);
        PatientDocument::query()
            ->whereKey($document->internal_id)
            ->each(function (PatientDocument $patientDocument) {
                $patientDocument->delete();
            });
    }
}

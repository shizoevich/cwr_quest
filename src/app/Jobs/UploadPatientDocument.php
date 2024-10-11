<?php

namespace App\Jobs;

use App\Events\PatientDocumentUpload;
use App\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class UploadPatientDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $file;

    private $documentOptions;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($file, array $documentOptions)
    {
        $this->file = $file;
        $this->documentOptions = $documentOptions;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $documentOptions = $this->documentOptions;
        $patientId = $documentOptions['patient_id'];
        $documentTypeId = isset($documentOptions['document_type_id']) ? $documentOptions['document_type_id'] : null;
        $onlyForAdmin = isset($documentOptions['only_for_admin']) ? $documentOptions['only_for_admin'] : false;
        $visible = isset($documentOptions['visible']) ? $documentOptions['visible'] : true;

        $file = $this->file;
        $originalFileName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $newFileName = md5(uniqid(time())) . '.' . $extension;
        $patient = Patient::findOrFail($patientId);

        Storage::disk('patients_docs')->put($newFileName, file_get_contents($file));
        dispatch(new MakeImageThumbnail($newFileName));

        $document = $patient->documents()->create([
            'document_type_id' => $documentTypeId,
            'original_document_name' => $originalFileName,
            'aws_document_name' => $newFileName,
            'only_for_admin' => $onlyForAdmin,
            'visible' => $visible,
        ]);

        event(new PatientDocumentUpload($document));

        return $document;
    }
}

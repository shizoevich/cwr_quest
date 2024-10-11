<?php

namespace App\Jobs\Documents;

use App\AssessmentForm;
use App\Events\Patient\DocZipArchiveGenerated;
use App\Helpers\Constant\DocZipArchiveConst;
use App\Models\Patient\DocumentRequest\DocumentZipArchive;
use App\Models\Patient\PatientElectronicDocument;
use App\User;
use App\Patient;
use App\PatientDocument;
use App\PatientDocumentComment;
use App\PatientDocumentType;
use App\PatientNote;
use DateTime;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class GeneratePatientDocZipArchive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;
    private $patient;
    private $documentTypes;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param Patient $patient
     * @param array $documentTypes
     * @return void
     */
    public function __construct(
        User $user,
        Patient $patient,
        array $documentTypes
    ) {
        $this->user = $user;
        $this->patient = $patient;
        $this->documentTypes = $documentTypes;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->documentTypes as $type) {
            if ($type === DocZipArchiveConst::TYPE_INITIAL_ASSESSMENT) {
                $this->processInitialAssessmentDocuments();
            } elseif ($type === DocZipArchiveConst::TYPE_PATIENT_NOTE) {
                $this->processPatientNoteDocuments();
            } elseif ($type === DocZipArchiveConst::TYPE_DISCHARGE_SUMMARY) {
                $this->processDischargeSummaryDocuments();
            }
        }

        $this->makeZipArchive(DocZipArchiveConst::ZIP_STORAGE);
    }

    private function processInitialAssessmentDocuments()
    {
        $patientDocuments = PatientDocument::select(['id', 'aws_document_name', 'created_at'])
            ->where('patient_id', $this->patient->id)
            ->whereIn('document_type_id', PatientDocumentType::getFileTypeIDsLikeInitialAssessment())
            ->get();

        if ($patientDocuments->isNotEmpty()) {
            foreach ($patientDocuments as $patientDocument) {
                $this->processDocuments(
                    $patientDocument->aws_document_name,
                    $patientDocument->created_at->format('Y-m-d'),
                    DocZipArchiveConst::TYPE_INITIAL_ASSESSMENT,
                    'patients_docs',
                    '.pdf'
                );
                $this->addDocumentCommentLogs($patientDocument->id, PatientDocument::class);
            }
        }

        $patientElectronicDocuments = PatientElectronicDocument::select(['id', 'document_type_id', 'created_at'])
            ->where('patient_id', $this->patient->id)
            ->whereIn('document_type_id', AssessmentForm::getFileTypeIDsLikeInitialAssessment())
            ->get();

        if ($patientElectronicDocuments->isNotEmpty()) {
            foreach ($patientElectronicDocuments as $patientElectronicDocument) {
                $this->processDocuments(
                    $patientElectronicDocument->id . '.docx',
                    $patientElectronicDocument->created_at->format('Y-m-d'),
                    DocZipArchiveConst::TYPE_INITIAL_ASSESSMENT,
                    'patient_assessment_forms',
                    '.docx'
                );
                $this->addDocumentCommentLogs($patientElectronicDocument->id, PatientElectronicDocument::class);
            }
        }
    }

    private function processPatientNoteDocuments()
    {
        $patientNotes = PatientNote::select(['id', 'date_of_service'])
            ->where('is_finalized', 1)
            ->where('patients_id', $this->patient->id)
            ->get();

        if ($patientNotes->isNotEmpty()) {
            foreach ($patientNotes as $patientNote) {
                $this->processDocuments(
                    $patientNote->id . '.pdf',
                    $patientNote->date_of_service,
                    DocZipArchiveConst::TYPE_PATIENT_NOTE,
                    'progress_notes',
                    '.pdf'
                );
                $this->addDocumentCommentLogs($patientNote->id, PatientNote::class);
            }
        }
    }

    private function processDischargeSummaryDocuments()
    {
        $patientDocuments = PatientDocument::select(['id', 'aws_document_name', 'created_at'])
            ->where('patient_id', $this->patient->id)
            ->whereIn('document_type_id', PatientDocumentType::getFileTypeIDsLikeDischarge())
            ->get();

        if ($patientDocuments->isNotEmpty()) {
            foreach ($patientDocuments as $patientDocument) {
                $this->processDocuments(
                    $patientDocument->aws_document_name,
                    $patientDocument->created_at->format('Y-m-d'),
                    DocZipArchiveConst::TYPE_DISCHARGE_SUMMARY,
                    'patients_docs',
                    '.pdf'
                );
                $this->addDocumentCommentLogs($patientDocument->id, PatientDocument::class);
            }
        }

        $patientElectronicDocuments = PatientElectronicDocument::select(['id', 'document_type_id', 'created_at'])
            ->where('patient_id', $this->patient->id)
            ->whereIn('document_type_id', AssessmentForm::getFileTypeIDsLikeDischarge())
            ->get();

        if ($patientElectronicDocuments->isNotEmpty()) {
            foreach ($patientElectronicDocuments as $patientElectronicDocument) {
                $this->processDocuments(
                    $patientElectronicDocument->id . '.docx',
                    $patientElectronicDocument->created_at->format('Y-m-d'),
                    DocZipArchiveConst::TYPE_DISCHARGE_SUMMARY,
                    'patient_assessment_forms',
                    '.docx'
                );

                $this->addDocumentCommentLogs($patientElectronicDocument->id, PatientElectronicDocument::class);
            }
        }
    }

    private function processDocuments(string $awsDocName, string $docCreatedDate, string $docType, string $storageName, string $fileExtension)
    {
        $fileContent = Storage::disk($storageName)->get($awsDocName);
        $filename = $this->patient->getFullName() . '__' . $docType . '__' . $docCreatedDate . $fileExtension;
        $uniqueFolderPath = $this->getUniqueFolderPath($this->patient->id, $this->patient->getFullName());
        Storage::disk(DocZipArchiveConst::ZIP_STORAGE)->put($uniqueFolderPath . '/' . $docType . '/' . $filename, $fileContent);
        sleep(2);
    }

    private function addDocumentCommentLogs(int $patientDocumentsId, string $documentModel): void
    {
        $adminId = null;
        $providerId = null;
        if ($this->user->isAdmin()) {
            $adminId = $this->user->id;
            $recipient = implode(' ', array($this->user->meta->firstname, $this->user->meta->lastname));
        } elseif ($this->user->isProvider()) {
            $providerId = $this->user->provider->id;
            $recipient = $this->user->provider->provider_name;
        }
        $commentContent = __('comments.document_download_by', [
            'recipient' => $recipient,
        ]);

        PatientDocumentComment::create([
            'patient_documents_id' => $patientDocumentsId,
            'document_model' => $documentModel,
            'admin_id' => $adminId,
            'provider_id' => $providerId,
            'content' => $commentContent,
            'is_system_comment' => '1',
        ]);
    }

    private function makeZipArchive(string $diskName)
    {
        try {
            $zip = new ZipArchive();
            //set zip File Name
            $uuid = \Uuid::generate()->string;
            $dateTime = new DateTime();
            $unixTimestamp = strtotime($dateTime->format('Y-m-d H:i:s'));
            $zipFileName = $uuid . '-' . $unixTimestamp . '.zip';
            //set unique folder path
            $uniqueFolderPath = $this->getUniqueFolderPath($this->patient->id, $this->patient->getFullName());

            if ($zip->open(Storage::disk($diskName)->path($zipFileName), ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $allDirectories = Storage::disk($diskName)->allDirectories($uniqueFolderPath);

                foreach ($allDirectories as $directory) {
                    $relativePath = substr($directory, strlen($uniqueFolderPath) + 1);
                    $zip->addEmptyDir($relativePath);

                    $filesInDirectory = Storage::disk($diskName)->allFiles($directory);
                    foreach ($filesInDirectory as $file) {
                        $relativeFilePath = substr($file, strlen($uniqueFolderPath) + 1);
                        $zip->addFile(Storage::disk($diskName)->path($file), $relativeFilePath);
                    }
                }
                $zip->close();
                Storage::disk($diskName)->deleteDirectory($uniqueFolderPath);

                $documentZipArchive = DocumentZipArchive::create([
                    'patient_id' => $this->patient->id,
                    'user_id' => $this->user->id,
                    'zip_file_unique_name' => $zipFileName,
                ]);
                event(new DocZipArchiveGenerated($documentZipArchive));
            }
        } catch (Exception $e) {
            \App\Helpers\SentryLogger::captureException($e);
            return null;
        }
    }

    private function getUniqueFolderPath($patientId, $patientFullName)
    {
        $fullName = preg_replace_callback('/(?:^|\s)(\w+)/u', function ($matches) {
            return ucfirst($matches[1]);
        }, $patientFullName);
        $fullName = str_replace(' ', '_', $fullName);
        $uniqueFolderPath = $fullName . '_' . $patientId;

        return $uniqueFolderPath;
    }
}

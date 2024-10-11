<?php

namespace App\Repositories\Patient\PatientDocZipGenerate;

use App\Helpers\Constant\DocZipArchiveConst;
use App\Jobs\Documents\GeneratePatientDocZipArchive;
use App\Models\Patient\DocumentRequest\DocumentZipArchive;
use App\Patient;
use App\PatientComment;
use Illuminate\Support\Facades\Auth;

class PatientDocZipGenerateRepository implements PatientDocZipGenerateRepositoryInterface
{
    /**
     * @param array $documentTypeData
     * @param Patient $patient
     * @return void
     */
    public function createPatientDocZip(array $documentTypeData, Patient $patient): void
    {
        if (!Auth::user()->isAdmin()) {
           return;
        }

        $documentTypeDataArray = array_keys(array_filter(json_decode($documentTypeData['documents'], true)['documentType'], function ($value) {
            return $value === true;
        }));

        dispatch(
            with(
                new GeneratePatientDocZipArchive(
                    Auth::user(),
                    $patient,
                    $documentTypeDataArray
                )
            )
        )->onQueue('zip-archive');
    }

    public function getPatientDocZip(Patient $patient, string $fileName)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $latestZip = DocumentZipArchive::where([
                'patient_id' => $patient->id,
                'zip_file_unique_name' => $fileName,
            ])->firstOrFail();
            $zipFileName = $latestZip->zip_file_unique_name;
            $zipFilePath = storage_path(DocZipArchiveConst::ZIP_FILE_PATH . $zipFileName);
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename=' . $zipFileName);
            header('Content-Length: ' . filesize($zipFilePath));
            readfile($zipFilePath);
            unlink($zipFilePath);
            $this->makeCommentInPatientChart($patient->id);
            return response()->json(['zipFilePath' => $zipFilePath]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'File not found'], 404);
        }
    }

    /**
     * @param int $patientId
     * @return void
     */
    private function makeCommentInPatientChart(int $patientId): void
    {
        PatientComment::create([
            'patient_id' => $patientId,
            'admin_id' => Auth::id(),
            'comment' => DocZipArchiveConst::COMMENT_PATIENT_CHART,
            'comment_type' => PatientComment::DEFAULT_COMMENT_TYPE,
        ]);
    }
}

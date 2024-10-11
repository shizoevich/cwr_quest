<?php


namespace App\Http\Controllers\Utils;


use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\Shared\ZipArchive;

trait ZipUtils
{
    /**
     * @param $documents
     * @param string $zipName
     * @return string|null
     */
    public function createPatientDocumentsArchive($documents, $zipName)
    {
        $zip = new ZipArchive();
        if ($zip->open(Storage::disk('temp_patient_forms')->getAdapter()->getPathPrefix() . $zipName,
            ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $counter = 1;
            foreach ($documents as $document) {
                $zip->addFromString($counter . ' - ' . $document->original_document_name, Storage::disk('patients_docs')->get($document->aws_document_name));
                $counter++;
            }
            try {
                $zip->close();
                ob_clean();
                return $zip->filename;
            } catch (Exception $e) {
                \App\Helpers\SentryLogger::captureException($e);
                return null;
            }
        }
        return null;
    }
}

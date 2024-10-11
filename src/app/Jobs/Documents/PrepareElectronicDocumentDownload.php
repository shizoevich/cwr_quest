<?php

namespace App\Jobs\Documents;

use App\Http\Controllers\Utils\AccessUtils;
use App\Jobs\Patients\AssessmentForms\Generate;
use App\Models\Patient\PatientElectronicDocument;
use App\PatientDocument;
use App\Scopes\PatientDocuments\DocumentsForAllScope;
use Illuminate\Contracts\Queue\ShouldQueue;


class PrepareElectronicDocumentDownload extends DocumentDownload implements ShouldQueue
{
    use AccessUtils;

    /**
     * @return array|int|null
     */
    public function handle()
    {
        $document = PatientElectronicDocument::find($this->document_id);
        $fileName = $this->document_id . '.docx';

        if (!$this->isUserHasAccessRightsForElectronicDocument($document)) {
            abort(403);
        }

        return $this->generateIfNotExists($document, $fileName);
    }


    private function generateIfNotExists(&$document, $fileName, $break = false) {
        if ($this->disk->exists($fileName)) {
            $file = $this->disk->get($fileName);
            $mime = $this->disk->mimeType($fileName);
            $originalName = $document->patient->first_name . ' ' . $document->patient->last_name . ' - ' . $document->type->document_name . '.docx';

            return [
                'file'         => $file,
                'mime'         => $mime,
                'documentName' => $originalName,
                'originalDocument' => $document,
            ];
        } else {
            if($break) {
                return null;
            } else {
                \Bus::dispatchNow(new Generate($document, $document->type->password));
                return $this->generateIfNotExists($document, $fileName, true);
            }
        }
    }
}

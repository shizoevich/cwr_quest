<?php

namespace App\Jobs\Documents;

use App\Http\Controllers\Utils\DocUtils;
use App\PatientAssessmentForm;
use Illuminate\Contracts\Queue\ShouldQueue;

class PrepareAssessmentDownload extends DocumentDownload implements ShouldQueue
{
    use DocUtils;


    /**
     * @return array|int|null
     */
    public function handle()
    {
        $document     = PatientAssessmentForm::find($this->document_id);
        $fileName = 'urn:oid:' . $document->s3_file_id;


        if ($document->has_signature && ! $document->signed) {
            $this->signDocx($fileName, $document->creator_id);
            $document->signed = true;
            $document->save();
        }

        if($this->isFileFaxSizeExceeds($fileName)){
            return -1;
        }

        if ($this->disk->exists($fileName)) {
            $file = $this->disk->get($fileName);
            $mime = $this->disk->mimeType($fileName);


            $patient      = $document->patient;
            $ext          = explode('.',
                $document->assessmentFormTemplate->file_name);
            $ext          = end($ext);
            $originalName = $patient->first_name . ' ' . $patient->last_name
                . ' - ' .
                $document->assessmentFormTemplate->title . '.' . $ext;

            return [
                'file'         => $file,
                'mime'         => $mime,
                'documentName' => $originalName
            ];
        } else {
            return null;
        }
    }
}

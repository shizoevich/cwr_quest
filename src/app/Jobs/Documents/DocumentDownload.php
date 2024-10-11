<?php

namespace App\Jobs\Documents;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class DocumentDownload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $document_id;
    protected $disk;

    /**
     * DocumentDownload constructor.
     *
     * @param $document_id
     */
    public function __construct($document_id)
    {
        $this->document_id = $document_id;
        $class = get_called_class();
        switch (true){
            case (strpos($class,"ElectronicDocumentDownload") != null):
                $this->disk = Storage::disk('patient_assessment_forms');
                break;
            case (strpos($class,"DocumentDownload") != null):
                $this->disk = Storage::disk('patients_docs');
                break;
            case (strpos($class,"AssessmentDownload") != null):
                $this->disk = Storage::disk('nextcloud');
                break;
            case (strpos($class,"NoteDownload") != null):
                $this->disk = Storage::disk('progress_notes');
                break;
            default:
                break;
        }

    }

    protected function isFileFaxSizeExceeds($filename)
    {
        $route = optional(\Request::route())->getName();

        if($this->disk->exists($filename)
            && $this->disk->getSize($filename) > config('ringcentral.max_file_size')
            && $route == 'patient.document-fax-send'){
            return true;
        } else {
            return false;
        }
    }

}

<?php

namespace App\Jobs\Comments;

use App\PatientDocumentShared;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WriteComment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $document;
    protected $data;
    protected $directDownload;
    protected $uniqueId;

    /**
     * WriteComment constructor.
     *
     * @param $document
     * @param $directDownload
     * @param $uniqueId
     */
    public function __construct($document, $directDownload, $uniqueId = null)
    {
        $this->document = $document;
        $this->directDownload = $directDownload;
        $this->uniqueId = $uniqueId;

        if($directDownload){
            $this->data     = [
                'patient_documents_id' => $document->id,
                'provider_id'          => \Auth::user()->provider ? \Auth::user()->provider->id : null,
                'admin_id'             => \Auth::user()->provider ?  null : \Auth::user()->id,
                'document_model'       => get_class($document),
            ];
        } else {
            $this->data     = [
                'patient_documents_id' => $document->patient_documents_id,
                'provider_id'          => $document->provider_id,
                'admin_id'             => $document->admin_id,
                'document_model'       => $document->document_model,
            ];
        }

    }

}

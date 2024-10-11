<?php

namespace App\Jobs\Fax;

use App\PatientNote;
use App\PatientDocument;
use App\PatientElectronicDocument; // do not change to App\Models\Patient\PatientElectronicDocument;
use App\PatientAssessmentForm;
use App\Services\Ringcentral\RingcentralFax;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Jobs\Documents\PrepareNoteDownload;
use App\Jobs\Documents\PrepareDocumentDownload;
use App\Jobs\Documents\PrepareElectronicDocumentDownload;
use App\Jobs\Documents\PrepareAssessmentDownload;

class SendDocumentFax implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $model;
    private $data;
    private $recipient;

    /**
     * SendDocumentFax constructor
     *
     * @param [type] $recipient
     * @param [type] $fax
     */
    public function __construct($recipient, $data)
    {
        $this->model = 'App\\' . $data['document_model'];
        $this->data  = $data;
        $this->recipient = $recipient;
    }

    public function handle()
    {
        switch ($this->model) {
            case PatientNote::class:
                $preparedData = \Bus::dispatchNow(new PrepareNoteDownload($this->data['patient_documents_id']));
                break;
            case PatientDocument::class:
                $preparedData = \Bus::dispatchNow(new PrepareDocumentDownload($this->data['patient_documents_id']));
                break;
            case PatientElectronicDocument::class:
                $preparedData = \Bus::dispatchNow(new PrepareElectronicDocumentDownload($this->data['patient_documents_id']));
                break;
            case PatientAssessmentForm::class:
                $preparedData = \Bus::dispatchNow(new PrepareAssessmentDownload($this->data['patient_documents_id']));
                break;
            default:
                return response(__('download.no_document'), 500);
                break;
        }

        if ($preparedData !== null && $preparedData !== -1) {
            $file = $preparedData['file'];
            $documentName = $preparedData['documentName'];

            $coverIndex = $this->getCoverIndex($preparedData['originalDocument'] ?? null);

            
            $ringcentralService = new RingcentralFax();
            $response = $ringcentralService->store($this->recipient, $file, $documentName, $coverIndex);
            
            return $response['id'];
        }

        return $preparedData;
    }

    private function getCoverIndex($document)
    {
        if (isset($document)) {
            if ($this->model === PatientElectronicDocument::class) {
                $meta = optional($document->type)->meta;
                if (isset($meta)) {
                    $meta = json_decode($meta, true);
                    $removeFaxCover = $meta['removeFaxCover'] ?? false;

                    return $removeFaxCover ? 0 : null;
                }
            }
        }

        return null;
    }
}

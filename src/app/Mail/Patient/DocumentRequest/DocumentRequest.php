<?php

namespace App\Mail\Patient\DocumentRequest;

use App\Models\Patient\DocumentRequest\PatientDocumentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DocumentRequest extends Mailable
{
    use Queueable, SerializesModels;

    private $hash;
    /**
     * @var PatientDocumentRequest
     */
    private $documentRequest;
    private $items;
    private $patient;
    
    public $subject = 'Re: Upcoming Appointment Documentation Request';
    
    /**
     * Create a new message instance.
     *
     * @param PatientDocumentRequest $documentRequest
     * @param                        $items
     * @param                        $patient
     */
    public function __construct(PatientDocumentRequest $documentRequest, $items, $patient)
    {
        $this->documentRequest = $documentRequest;
        $this->items = $items;
        $this->patient = $patient;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.patients.document_request', [
            'hash' => $this->documentRequest->hash,
            'items' => $this->items,
            'patient' => $this->patient,
        ]);
    }
}

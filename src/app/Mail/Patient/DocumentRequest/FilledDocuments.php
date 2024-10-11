<?php

namespace App\Mail\Patient\DocumentRequest;

use App\Models\Patient\DocumentRequest\PatientDocumentRequestSharedDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class FilledDocuments extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var PatientDocumentRequestSharedDocument
     */
    private $shared;
    
    public $subject = 'Requested copy of signed document';
    
    /**
     * FilledDocuments constructor.
     *
     * @param PatientDocumentRequestSharedDocument $shared
     */
    public function __construct(PatientDocumentRequestSharedDocument $shared)
    {
        $this->shared = $shared;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.patients.filled_documents', [
            'hash' => $this->shared->hash,
            'items' => $this->shared->documentRequest->items,
            'patient' => $this->shared->documentRequest->patient,
        ]);
    }
}

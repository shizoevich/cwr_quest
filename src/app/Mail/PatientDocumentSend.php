<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class PatientDocumentSend extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * @var \App\PatientDocument[]|Collection
     */
    private $documents;

    /**
     * Create a new message instance.
     *
     * @param Collection $documents
     */
    public function __construct(Collection $documents)
    {
        $this->documents = $documents;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->markdown('emails.patients.patient_info_document')
            ->from('admin@cwr.care', 'Change Within Reach, Inc.')
            ->subject('Secure Email from Change Within Reach, Inc.');
        foreach ($this->documents as $document) {
            $data = Storage::disk('patients_docs')->get($document->aws_document_name);
            $mimeType = Storage::disk('patients_docs')->mimeType($document->aws_document_name);
            $mail->attachData($data, $document->original_document_name, ['mime' => $mimeType]);
        }
        return $mail;
    }
}

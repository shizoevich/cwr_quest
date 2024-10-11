<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PatientDocument extends Mailable
{
    use Queueable, SerializesModels;

    public $sender;
    public $documentUrl;
    /**
     * PatientDocument constructor.
     *
     * @param $documentUrl
     * @param $provider
     */
    public function __construct($documentUrl, $sender)
    {
        $this->sender = $sender;
        $this->documentUrl = $documentUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.patients.document', ['provider' => $this->sender->name, 'documentUrl' => $this->documentUrl])
            ->from('admin@cwr.care', 'Change Within Reach, Inc.')
            ->subject('Secure Email from Change Within Reach, Inc.');
    }
}

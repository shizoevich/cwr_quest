<?php

namespace App\Mail\Patient;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DocumentDownloadRequest extends Mailable
{
    use Queueable, SerializesModels;

    public  $subject = 'Secure Email from Change Within Reach, Inc.';
    private $url;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.patients.document', [
            'documentUrl' => $this->url,
        ]);
    }
}

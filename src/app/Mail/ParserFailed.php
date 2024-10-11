<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ParserFailed extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * @var
     */
    private $message;
    /**
     * @var array
     */
    private $data;

    /**
     * Create a new message instance.
     *
     * @param $message
     * @param array $data
     */
    public function __construct($message, array $data = [])
    {
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.parser-failed', [
            'message' => $this->message,
            'data' => '<pre>' . print_r($this->data, true) . '</pre>'
        ]);
    }
}

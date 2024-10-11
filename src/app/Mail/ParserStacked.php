<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ParserStacked extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * @var array
     */
    private $data;
    
    /**
     * ParserStacked constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.parser-stacked', [
            'data' => $this->data
        ]);
    }
}

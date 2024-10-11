<?php

namespace App\Mail\Patient;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FirstAppointment extends Mailable
{
    use Queueable, SerializesModels;

    private $formattedDate;

    private $officeName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $formattedDate, string $officeName)
    {
        $this->formattedDate = $formattedDate;
        $this->officeName = $officeName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.patients.first_appointment', [
            'formattedDate' => $this->formattedDate,
            'officeName' => $this->officeName,
        ]);
    }
}

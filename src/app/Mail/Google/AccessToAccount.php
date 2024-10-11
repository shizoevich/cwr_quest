<?php

namespace App\Mail\Google;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccessToAccount extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * @var string
     */
    private $email;
    /**
     * @var string
     */
    private $password;

    public $subject = "CWR Registration";

    /**
     * Create a new message instance.
     *
     * @param string $email
     * @param string $password
     */
    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.google.access-to-account', [
            'email'    => $this->email,
            'password' => $this->password,
        ]);
    }
}

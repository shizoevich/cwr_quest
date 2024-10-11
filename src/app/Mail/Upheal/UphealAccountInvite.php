<?php

namespace App\Mail\Upheal;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UphealAccountInvite extends Mailable
{
    use Queueable, SerializesModels;

    private $providerName;

    private $inviteUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $providerName, string $inviteUrl)
    {
        $this->providerName = $providerName;
        $this->inviteUrl = $inviteUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.upheal.account-invite', [
            'providerName' => $this->providerName,
            'inviteUrl' => $this->inviteUrl,
        ]);
    }
}

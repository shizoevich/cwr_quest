<?php

namespace App\Transports\Mail;

use App\Exceptions\Email\EmailNotSentException;
use Illuminate\Mail\Transport\Transport;
use MailchimpTransactional\ApiClient;
use MailchimpTransactional\ApiException;
use App\Models\MandrillRejectedEmail;
use App\Helpers\MandrillHelper;
use App\Exceptions\Email\EmailInRejectListException;
use Swift_Mime_SimpleMessage;

class MandrillTransport extends Transport
{
    private $config;
    
    private $client;
    
    public function __construct($config)
    {
        $this->config = $config;
    }
    
    /**
     * @return ApiClient
     * @throws ApiException
     */
    private function getClient(): ApiClient
    {
        if (!$this->client) {
            $this->client = new ApiClient();
            $this->client->setApiKey($this->config['secret']);
        }
        
        return $this->client;
    }
    
    /**
     * @throws EmailNotSentException
     * @throws ApiException
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);
        $fromEmail = key($message->getFrom());
        $fromName = $message->getFrom()[$fromEmail];
        $recipients = array_keys($message->getTo());
        $message = [
            'html' => $message->getBody(),
            'subject' => $message->getSubject(),
            'from_email' => $fromEmail,
            'from_name' => $fromName,
            'to' => [],
        ];
        foreach ($recipients as $recipient) {
            $message['to'][] = [
                'email' => $recipient,
            ];
        }
        $result = $this->getClient()->messages->send([
            'message' => $message,
            'async' => false,
        ]);

        /**
         * Possible values: "sent", "queued", "scheduled", "rejected", or "invalid".
         * @see https://mailchimp.com/developer/transactional/api/messages/send-new-message/
         */

        if ($result[0]->status === 'rejected' || $result[0]->status === 'invalid') {
            if (count($recipients)) {
                $toEmail = $recipients[0];
                $reject = MandrillHelper::findEmailInRejectList($toEmail);
                if (isset($reject) && $reject->reason === 'unsub') {
                    $rejectedEmail = MandrillRejectedEmail::where('email', $toEmail)->first();
                    if (isset($rejectedEmail)) {
                        $rejectedEmail->update([
                            'rejection_times' => $rejectedEmail->rejection_times + 1
                        ]);

                        if (!$rejectedEmail->is_restored) {
                            throw new EmailInRejectListException($toEmail);
                        }
                    } else {
                        MandrillRejectedEmail::create([
                            'email' => $toEmail,
                            'rejection_times' => 1
                        ]);

                        throw new EmailInRejectListException($toEmail);
                    }
                }
            }

            throw new EmailNotSentException();
        }

        // reset rejection_times after successfully sending an email
        foreach ($recipients as $recipient) {
            $rejectedEmail = MandrillRejectedEmail::where('email', $recipient)->first();
            if (isset($rejectedEmail)) {
                $rejectedEmail->update([
                    'rejection_times' => 0
                ]);
            }
        }

        return $result;
    }
}
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Channels\SlackWebhookChannel;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Throwable;

class AnErrorOccurred extends Notification
{
    use Queueable;
    
    public $message;

    public $trace;
    
    public $originalException;
    
    /**
     * OfficeAllyParserFailed constructor.
     *
     * @param $message
     * @param $trace
     */
    public function __construct($exception)
    {
        $message = 'Undefined Error';
        $trace = null;
        $originalException = null;
        if (is_string($exception)) {
            $message = $exception;
        } else if (is_object($exception) && $exception instanceof Throwable) {
            $message = $exception->getMessage();
            $trace = $exception->getTraceAsString();
            $originalException = $exception;
        }

        $this->message = $message;
        $this->trace = $trace;
        $this->originalException = $originalException;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // return [SlackWebhookChannel::class];
        return [];
    }

    public function toSlack($notifiable)
    {
        // return (new SlackMessage)
        //     ->attachment(function($attachment) {
        //         $attachment->title($this->message)
        //             ->content($this->trace);
        //     });
        return null;
    }
}

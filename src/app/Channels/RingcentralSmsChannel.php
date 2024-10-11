<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use App\Services\Twilio\TwilioLookup;
use App\Services\Ringcentral\RingcentralSms;
use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;

class RingcentralSmsChannel
{
    public function send($notifiable, Notification $notification)
    {
        try {
            $to = $notifiable->routeNotificationFor('ringcentral_sms');
            $message = $notification->toRingcentralMessage($notifiable);

            TwilioLookup::validatePhone($to);

            $smsService = new RingcentralSms();
            $smsService->store([$to], $message->content);
        } catch (\Exception $e) {
            \Log::error($e->getMessage() . $e->getTraceAsString());
            with(new ExceptionNotificator())->notifyAndSendToSentry(new AnErrorOccurred($e));
    
            return null;
        }
    }
}
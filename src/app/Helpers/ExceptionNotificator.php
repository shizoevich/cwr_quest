<?php

namespace App\Helpers;

use Illuminate\Notifications\Notifiable;
use App\Helpers\SentryLogger;

class ExceptionNotificator
{
    use Notifiable;

    private function formatInstanceMessage($message, $trace)
    {
        if (!isset($message)) {
            return null;
        }
        if (!isset($trace)) {
            return $message;
        }

        return $message . "\n\>>>>>>>>\nTRACE:\n" . $trace;
    }

    public function officeAllyNotifyAndSendToSentry($instance, $tags = [])
    {
        if (config('app.env') === 'testing') {
            return;
        }

        $this->notify($instance);

        if (isset($instance->originalException)) {
            SentryLogger::officeAllyCaptureException($instance->originalException, $tags);
        } else if (isset($instance->message)) {
            SentryLogger::officeAllyCaptureMessage($this->formatInstanceMessage($instance->message, $instance->trace ?? null), $tags);
        }
    }

    public function tridiuumNotifyAndSendToSentry($instance, $tags = [])
    {
        if (config('app.env') === 'testing') {
            return;
        }

        $this->notify($instance);

        if (isset($instance->originalException)) {
            SentryLogger::tridiuumCaptureException($instance->originalException, $tags);
        } else if (isset($instance->message)) {
            SentryLogger::tridiuumCaptureMessage($this->formatInstanceMessage($instance->message, $instance->trace ?? null), $tags);
        }
    }

    public function notifyAndSendToSentry($instance)
    {
        if (config('app.env') === 'testing') {
            return;
        }
        
        $this->notify($instance);

        if (isset($instance->originalException)) {
            SentryLogger::captureException($instance->originalException);
        } else if (isset($instance->message)) {
            SentryLogger::captureMessage($this->formatInstanceMessage($instance->message, $instance->trace ?? null));
        }
    }
    
    public function routeNotificationForSlack()
    {
        return config('services.slack.webhook_url');
    }
}
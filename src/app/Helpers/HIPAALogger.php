<?php

namespace App\Helpers;

use Carbon\Carbon;

class HIPAALogger
{
    private static $queue;

    private static function getQueue()
    {
        if (!isset(self::$queue)) {
            $queueManager = app('queue');
            self::$queue = $queueManager->connection('rabbitmq');
        }

        return self::$queue;
    }

    public static function logEvent($params)
    {
        if (config('app.env') !== 'production') {
            return;
        }

        $user = optional(auth()->user());
        $event = [
            'appeared_at' => Carbon::now()->toDateTimeString(),
            'client_ip' => request()->ip(),
            'source' => request()->header('host'),
            'user_id' => $user->id ?? -1,
            'username' => $user->email ?? 'system',
            'user_agent' => request()->header('user-agent'),
        ];

        try {
            $queue = self::getQueue();
    	    $queue->pushRaw(json_encode(array_merge($event, $params)));
        } catch (\Exception $e) {
            \Log::error($e->getMessage() . $e->getTraceAsString());
            \App\Helpers\SentryLogger::captureException($e);
        }
    }
}

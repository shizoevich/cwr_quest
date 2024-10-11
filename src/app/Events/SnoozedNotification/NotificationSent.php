<?php

namespace App\Events\SnoozedNotification;

use Illuminate\Queue\SerializesModels;
use App\Models\ScheduledNotification;

/**
 * @see https://github.com/thomasjohnkane/snooze
 * Class NotificationSent
 * @package App\Events\SnoozedNotification
 */
class NotificationSent
{
    use SerializesModels;

    public $scheduledNotification;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\ScheduledNotification $scheduledNotification
     * @return void
     */
    public function __construct(ScheduledNotification $scheduledNotification)
    {
        $this->scheduledNotification = $scheduledNotification;
    }
}

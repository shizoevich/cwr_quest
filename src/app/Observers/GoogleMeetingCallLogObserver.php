<?php

namespace App\Observers;

use App\Models\GoogleMeetingCallLog;
use App\User;

class GoogleMeetingCallLogObserver
{
    /**
     * @param GoogleMeetingCallLog $callLog
     */
    public function creating(GoogleMeetingCallLog $callLog)
    {
        if(!$callLog->is_external && !GoogleMeetingCallLog::query()->where('is_external', 0)->where('google_meeting_id', $callLog->google_meeting_id)->exists()) {
            $callLog->is_initial = true;
        }
    }
}
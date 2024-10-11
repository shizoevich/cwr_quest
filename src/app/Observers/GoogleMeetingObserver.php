<?php

namespace App\Observers;

use App\Helpers\HIPAALogger;
use App\Jobs\Google\CalendarEvent\DeleteCalendarEvent;
use App\Models\GoogleMeeting;

class GoogleMeetingObserver
{
    /**
     * @param GoogleMeeting $meeting
     */
    public function deleting(GoogleMeeting $meeting)
    {
       dispatch(new DeleteCalendarEvent($meeting->calendar_event_external_id));
    }

    public function created(GoogleMeeting $meeting)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($meeting),
                'event' => 'create',
                'data' => $meeting->getLogData(),
                'message' => $meeting->getCreateLogMessage(),
            ]
        );
    }

    public function updated(GoogleMeeting $meeting)
    {
        $dirtyFields = $meeting->getDirtyWithOriginal();
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($meeting),
                'event' => 'update',
                'data' => $meeting->getLogData(),
                'dirty_fields' => $dirtyFields,
                'message' => $meeting->getUpdateLogMessage($dirtyFields),
            ]
        );
    }

    public function deleted(GoogleMeeting $meeting)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($meeting),
                'event' => 'delete',
                'data' => $meeting->getLogData(),
                'message' => $meeting->getDeleteLogMessage(),
            ]
        );
    }
}
<?php

namespace App\Jobs\Google\CalendarEvent;

use App\Helpers\Google\CalendarService;
use App\Models\GoogleMeeting;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;

class CheckConferencesCreationStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ConferenceTrait;
    
    /**
     * @var int|null
     */
    private $meetingId;
    
    /**
     * @var CalendarService
     */
    private $service;
    
    /**
     * CheckConferenceCreationStatus constructor.
     *
     * @param int|null $meetingId
     */
    public function __construct($meetingId = null)
    {
        $this->meetingId = $meetingId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $statuses = [GoogleMeeting::CONFERENCE_CREATE_PENDING, GoogleMeeting::CONFERENCE_CREATE_FAILED];

        GoogleMeeting::query()
            ->when(isset($this->meetingId), function($query) {
                $query->where('id', $this->meetingId);
            }, function($query) use ($statuses) {
                $query->whereIn('conference_creation_status', $statuses);
            })
            ->chunkById(1000, function(Collection $collection) {
                $collection->each(function(GoogleMeeting $meeting) {
                    if ($meeting->conference_creation_status === GoogleMeeting::CONFERENCE_CREATE_PENDING) {
                        $this->processPendingMeeting($meeting);
                    } else {
                        $this->processFailedMeeting($meeting);
                    }
                });
            });
    }

    private function processPendingMeeting(GoogleMeeting $meeting)
    {
        $conferenceData = $this->getConferenceData($meeting);
        
        if (!$conferenceData) {
            return;
        }
        
        $conferenceData = $this->prepareConferenceData($conferenceData);
        $meeting->update($conferenceData);
    }

    private function processFailedMeeting(GoogleMeeting $meeting)
    {
        $newMeetingData = \Bus::dispatchNow(new CreateCalendarEventForAppointmentWithoutSave($meeting->appointment));
        $meeting->update($newMeetingData);
    }
    
    private function getService()
    {
        if (!isset($this->service)) {
            $this->service = (new CalendarService())->getService();
        }
    
        return $this->service;
    }
    
    /**
     * @param GoogleMeeting $meeting
     *
     * @return \Google_Service_Calendar_ConferenceData|null
     */
    private function getConferenceData(GoogleMeeting $meeting)
    {
        $service = $this->getService();
        
        try {
            return $service->events->get(config('services.google.calendar_id'), $meeting->calendar_event_external_id)->getConferenceData();
        } catch(\Google_Service_Exception $e) {
            if ($e->getCode() !== 404) {
                \Log::error($e->getMessage(), $e->getTrace());
                \App\Helpers\SentryLogger::captureException($e);
            }
        }
        
        return null;
    }
}

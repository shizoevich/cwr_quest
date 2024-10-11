<?php

namespace App\Jobs\Google\CalendarEvent;

use App\Appointment;
use App\Helpers\Google\CalendarService;
use App\Models\GoogleMeeting;
use App\Patient;
use App\Provider;
use Carbon\Carbon;
use Google_Service_Calendar_Event;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

abstract class AbstractCreateCalendarEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ConferenceTrait;
    
    /** @var Patient */
    protected $patient;
    
    /** @var Provider */
    protected $provider;
    
    /** @var Appointment|null */
    protected $appointment;
    
    /** @var bool */
    protected $withConference;
    
    /** @var string */
    protected $conferenceRequestId;
    
    /**
     * Create a new job instance.
     *
     * @param Patient          $patient
     * @param Provider         $provider
     * @param Appointment|null $appointment
     * @param bool             $withConference
     */
    public function __construct(Patient $patient, Provider $provider, $appointment, bool $withConference = true)
    {
        $this->patient = $patient;
        $this->provider = $provider;
        $this->appointment = $appointment;
        $this->withConference = $withConference;
        $this->conferenceRequestId = uniqid(time());
    }

    /**
     * Execute the job.
     *
     * @return GoogleMeeting
     */
    public function handle()
    {
        $googleEvent = $this->createGoogleEvent();

        $event = GoogleMeeting::create($this->prepareEventData($googleEvent));
        if ($event->conferenceCreationPending()) {
            /**
             * Sleep needs because conference creation process is async
             */
            sleep(5);
            \Bus::dispatchNow(new CheckConferencesCreationStatus($event->getKey()));
        }

        return $event;
    }

    protected function createGoogleEvent(): Google_Service_Calendar_Event
    {
        $service = (new CalendarService())->getService();
        $optParams = [];
        if ($this->withConference) {
            $optParams['conferenceDataVersion'] = 1;
            $eventData = $this->getEventDataWithConference();
        } else {
            $eventData = $this->getEventData();
        }

        $googleEvent = new Google_Service_Calendar_Event($eventData);   
        
        /**
         * Conferences are created asynchronously
         * @see https://developers.google.com/calendar/create-events#conferencing
         */
        return $service->events->insert(config('services.google.calendar_id'), $googleEvent, $optParams);
    }

    /**
     * @param Google_Service_Calendar_Event $event
     *
     * @return array
     */
    protected function prepareEventData(Google_Service_Calendar_Event $event): array
    {
        $conferenceData = $this->prepareConferenceData($event->getConferenceData());

        return array_merge([
            'patient_id' => $this->patient->getKey(),
            'provider_id' => $this->provider->getKey(),
            'appointment_id' => optional($this->appointment)->getKey(),
            'calendar_event_external_id' => $event->getId(),
            'event_starts_at' => Carbon::parse($event->getStart()->getDateTime())->timezone($event->getStart()->getTimeZone())->toDateTimeString(),
            'event_ends_at' => Carbon::parse($event->getEnd()->getDateTime())->timezone($event->getEnd()->getTimeZone())->toDateTimeString(),
        ], $conferenceData);
    }
    
    /**
     * @return array
     */
    abstract protected function getEventData();
    
    /**
     * @return array
     */
    protected function getEventDataWithConference()
    {
        $eventData = $this->getEventData();
        $eventData['conferenceData'] = [
            'createRequest' => [
                'requestId' => $this->conferenceRequestId,
            ]
        ];
        
        return $eventData;
    }
}

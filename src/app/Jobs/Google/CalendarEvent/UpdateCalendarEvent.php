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
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateCalendarEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ConferenceTrait;

    /**
     * @var \Google_Service_Calendar
     */
    protected $service;

    /**
     * @var GoogleMeeting
     */
    protected $googleMeeting;

    /**
     * @var Appointment
     */
    protected $appointment;

    /**
     * @var Patient
     */
    protected $patient;

    /**
     * @var Provider
     */
    protected $provider;

    /**
     * UpdateCalendarEvent constructor.
     * @param GoogleMeeting $googleMeeting
     */
    public function __construct(GoogleMeeting $googleMeeting)
    {
        $this->service = (new CalendarService())->getService();
        $this->googleMeeting = $googleMeeting;
        $this->appointment = $googleMeeting->appointment;
        $this->patient = $googleMeeting->patient;
        $this->provider = $googleMeeting->provider;
    }

    /**
     * @return Google_Service_Calendar_Event
     */
    public function handle()
    {
        return $this->service->events->update(
            config('services.google.calendar_id'),
            $this->googleMeeting->calendar_event_external_id,
            new Google_Service_Calendar_Event($this->getEventData())
        );
    }

    /**
     * @return array
     */
    protected function getEventData()
    {
        $startDate = Carbon::createFromTimestamp($this->appointment->time);
        $endDate = $startDate->copy()->addMinutes((int)$this->appointment->visit_length > 0 ? $this->appointment->visit_length : 60);

        return [
            'summary' => sprintf(
                '%s %s (#%s) telehealth session (#%s) with %s',
                $this->patient->first_name,
                $this->patient->last_name,
                $this->patient->patient_id,
                $this->appointment->idAppointments,
                $this->provider->provider_name
            ),
            'start' => [
                'dateTime' => $startDate->toIso8601String(),
                'timeZone' => $startDate->getTimezone()->getName(),
            ],
            'end' => [
                'dateTime' => $endDate->toIso8601String(),
                'timeZone' => $endDate->getTimezone()->getName(),
            ],
        ];
    }
}
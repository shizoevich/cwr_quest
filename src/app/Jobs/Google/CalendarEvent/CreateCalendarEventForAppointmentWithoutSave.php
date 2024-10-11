<?php

namespace App\Jobs\Google\CalendarEvent;

use App\Appointment;
use Carbon\Carbon;

class CreateCalendarEventForAppointmentWithoutSave extends AbstractCreateCalendarEvent
{

    /**
     * Create a new job instance.
     *
     * @param Appointment $appointment
     * @param bool        $withConference
     */
    public function __construct(Appointment $appointment, bool $withConference = true)
    {
        parent::__construct($appointment->patient, $appointment->provider, $appointment, $withConference);
    }

    /**
     * Execute the job.
     *
     * @return GoogleMeeting
     */
    public function handle(): array
    {
        $googleEvent = $this->createGoogleEvent();

        return $this->prepareEventData($googleEvent);
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

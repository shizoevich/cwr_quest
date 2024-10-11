<?php

namespace App\Jobs\Google\CalendarEvent;

use App\Patient;
use App\Provider;
use Carbon\Carbon;

class CreateCalendarEventForPatient extends AbstractCreateCalendarEvent
{
    
    /**
     * CreateCalendarEventForPatient constructor.
     *
     * @param Patient  $patient
     * @param Provider $provider
     * @param bool     $withConference
     */
    public function __construct(Patient $patient, Provider $provider, bool $withConference = true)
    {
        parent::__construct($patient, $provider, null, $withConference);
    }
    
    /**
     * @return array
     */
    protected function getEventData()
    {
        $startDate = Carbon::now();
        $endDate = $startDate->copy()->addHour();
    
        return [
            'summary' => sprintf(
                '%s %s (#%s) telehealth session with %s',
                $this->patient->first_name,
                $this->patient->last_name,
                $this->patient->patient_id,
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

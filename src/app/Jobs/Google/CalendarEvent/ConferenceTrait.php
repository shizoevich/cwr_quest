<?php

namespace App\Jobs\Google\CalendarEvent;

use App\Models\GoogleMeeting;
use Google_Service_Calendar_EntryPoint;

trait ConferenceTrait
{
    /**
     * @param \Google_Service_Calendar_ConferenceData|null $conferenceData
     *
     * @return array
     */
    protected function prepareConferenceData($conferenceData)
    {
        $conferenceUri = null;
        $conferencePhone = null;
        $conferencePhonePin = null;
        $conferenceCreateRequest = optional($conferenceData)->getCreateRequest();
        if($conferenceData) {
            foreach ($conferenceData->getEntryPoints() as $entryPoint) {
                /** @var Google_Service_Calendar_EntryPoint $entryPoint */
                if($entryPoint->getEntryPointType() === 'video') {
                    $conferenceUri = $entryPoint->getUri();
                } else if($entryPoint->getEntryPointType() === 'phone' && $entryPoint->getRegionCode() === 'US') {
                    $conferencePhone = str_replace('tel:', '', $entryPoint->getUri());
                    $conferencePhonePin = $entryPoint->getPin();
                }
            }
        }
        $conferenceCreationStatus = 0;
        switch (optional(optional($conferenceCreateRequest)->getStatus())->getStatusCode()) {
            case 'success':
                $conferenceCreationStatus = GoogleMeeting::CONFERENCE_CREATED_SUCCESS;
                break;
            case 'pending':
                $conferenceCreationStatus = GoogleMeeting::CONFERENCE_CREATE_PENDING;
                break;
            case 'failure':
                $conferenceCreationStatus = GoogleMeeting::CONFERENCE_CREATE_FAILED;
                break;
        }
        
        return [
            'conference_request_external_id' => optional($conferenceCreateRequest)->getRequestId(),
            'conference_external_id' => optional($conferenceData)->getConferenceId(),
            'conference_uri' => $conferenceUri,
            'conference_phone' => $conferencePhone,
            'conference_phone_pin' => $conferencePhonePin,
            'conference_creation_status' => $conferenceCreationStatus,
        ];
    }
}

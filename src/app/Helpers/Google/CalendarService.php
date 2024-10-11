<?php

namespace App\Helpers\Google;

class CalendarService extends AbstractService
{

    /**
     * @return \Google_Service_Calendar
     */
    public function getService(): \Google_Service
    {
        if(empty($this->getScopes())) {
            $this->setScopes([\Google_Service_Calendar::CALENDAR_EVENTS]);
        }
        $client = $this->getClient();
        $client->addScope($this->getScopes());

        return new \Google_Service_Calendar($client);
    }
}
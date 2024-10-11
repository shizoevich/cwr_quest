<?php

namespace App\Helpers\Sites\OfficeAlly\Traits;

/**
 * Trait Appointments
 * @package App\Helpers\Sites\OfficeAlly\Traits
 */
trait Offices
{
    /**
     * @param $officeId
     *
     * @return mixed
     */
    public function getOfficeRooms($officeId)
    {
        $response = $this->officeAlly->post("CommonUserControls/Ajax/WebAPI/Api.aspx?method=GET&url=v1/appointments/offices/{$officeId}/resources",
            [
                'json' => [
                    'url' => "v1/appointments/offices/{$officeId}/resources",
                    'urlparam' => [
                        'officeid' => $officeId,
                        'typeid' => '5',
                    ],
                    'data' => [],
                    'method' => 'GET',
                    'contenttype' => null,
                    'headers' => [],
                    'type' => 1,
                    'usetoken' => true,
                ],
                'headers' => [
                    'X-OA-AUTH-TOKEN' => $this->getRequestVerificationToken()
                ]
            ], true);
        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);
        
        return json_decode(data_get($data, 'dt'), true);
    }
}
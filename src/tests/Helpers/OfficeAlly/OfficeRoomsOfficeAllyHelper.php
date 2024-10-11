<?php

namespace Tests\Helpers\OfficeAlly;

use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Office;

class OfficeRoomsOfficeAllyHelper
{
    public const OFFICE_ID = 1;
    public const OFFICE_ROOM_NAME = 'Test Room';

    public static function findOfficeRooms(OfficeAllyHelper $officeAllyHelper)
    {
        $offices = Office::query()->whereNotNull('external_id')->get();

        foreach ($offices as $office) {
            $rooms = $officeAllyHelper->getOfficeRooms($office->external_id);
            if (isset($rooms) && count($rooms)) {
                return $rooms;
            }
        }

        return null;
    }

    public static function getOfficeRoomDataFromJson($rooms)
    {
        $roomData = null;

        foreach ($rooms as $room) {
            $roomData = [
                'external_id' => $room['ID'],
                'name' => preg_replace('/\d ?- ?/', '', $room['Name'])
            ];

            break;
        }
        
        return $roomData;
    }

    public static function getStructureOfficeRoomData(): array
    {
        return [
            'external_id' => 'int',
            'name' => 'string',
        ];
    }

    public static function getOfficeRoomData(): array
    {
        return [
            'external_id' => 10035089,
            'name' => 'Blooming Garden'
        ];
    }

    public static function getMockOfficeRoomsData($roomData): array
    {
        return [
            [
                'ID' => $roomData['external_id'],
                'Name' => '1 - ' . $roomData['name']
            ],
        ];
    }
}
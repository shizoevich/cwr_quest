<?php

namespace App\Jobs\Parsers\Guzzle;

use App\DTO\OfficeAlly\OfficeRoomDTO;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Option;
use App\Office;
use App\OfficeRoom;

/**
 * Class PatientsParser
 * @package App\Jobs\Parsers\Guzzle
 */
class OfficeRoomsParser extends AbstractParser
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handleParser()
    {
        $officeAllyHelper = app()->make(OfficeAllyHelper::class)(Option::OA_ACCOUNT_2);
        Office::query()
            ->whereNotNull('external_id')
            ->each(function (Office $office) use ($officeAllyHelper) {
                $rooms = $officeAllyHelper->getOfficeRooms($office->external_id);
                if ($rooms === null) {
                    return;
                }
                $this->roomsCrawler($office, $rooms);
            });
    }

    /**
     * @param Office $office
     * @param array  $rooms
     */
    private function roomsCrawler(Office $office, array &$rooms)
    {
        foreach ($rooms as $room) {
            $roomName = preg_replace('/\d ?- ?/', '', $room['Name']);
            // OfficeRoom::updateOrCreate([
            //     'external_id' => $room['ID'],
            // ], [
            //     'office_id' => $office->id,
            //     'name' => $roomName,
            // ]);
            $roomName = preg_replace('/\d ?- ?/', '', $room['Name']);
            $officeRoomDTO = new OfficeRoomDTO([
                'external_id' => (string)$room['ID'],
                'office_id' => $office->id,
                'name' => $roomName,
            ]);

            OfficeRoom::updateOrCreate(
                ['external_id' => $officeRoomDTO->external_id],
                [
                    'office_id' => $officeRoomDTO->office_id,
                    'name' => $officeRoomDTO->name,
                ]
            );
        }
    }
}

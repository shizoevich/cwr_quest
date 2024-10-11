<?php

namespace Tests\Feature\Parsers;

use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Jobs\Parsers\Guzzle\OfficeRoomsParser;
use App\OfficeRoom;
use App\Option;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Tests\Traits\OfficeAlly\OfficeAllyTrait;
use Tests\Helpers\OfficeAlly\OfficeRoomsOfficeAllyHelper;

class OfficeRoomsParserTest extends TestCase
{
    use OfficeAllyTrait;

    protected const OA_ACCOUNT = Option::OA_ACCOUNT_2;    

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testOfficeAllyAuthorization()
    {
        Event::fake();

        self::authorizationTest($this, self::OA_ACCOUNT);
    }

    public function testOfficeRoomDataStructure()
    {
        Event::fake();

        $officeAllyHelper = new OfficeAllyHelper(self::OA_ACCOUNT);

        $rooms = OfficeRoomsOfficeAllyHelper::findOfficeRooms($officeAllyHelper);
        $roomData = OfficeRoomsOfficeAllyHelper::getOfficeRoomDataFromJson($rooms);

        foreach (OfficeRoomsOfficeAllyHelper::getStructureOfficeRoomData() as $key => $type) {
            $this->assertArrayHasKey($key, $roomData, "The key '$key' is missing in the appointment data.");
            $this->assertInternalType($type, $roomData[$key], "The value of '$key' does not correspond to type '$type'.");
        }
    }

    public function testCreateOfficeRoom()
    {
        Event::fake();

        $roomData = OfficeRoomsOfficeAllyHelper::getOfficeRoomData();

        $checkingData = [
            'external_id' => $roomData['external_id'],
            'name' => $roomData['name']
        ];

        $this->assertDatabaseMissing(self::TABLE_OFFICES_ROOMS, $checkingData);

        $mockData = OfficeRoomsOfficeAllyHelper::getMockOfficeRoomsData($roomData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getOfficeRooms', $mockData);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper;
        });

        (new OfficeRoomsParser())->handleParser();

        $this->assertDatabaseHas(self::TABLE_OFFICES_ROOMS, $checkingData);
    }

    public function testUpdateOfficeRoom()
    {
        Event::fake();

        $roomData = OfficeRoomsOfficeAllyHelper::getOfficeRoomData();

        OfficeRoom::create([
            'external_id' => $roomData['external_id'],
            'office_id' => OfficeRoomsOfficeAllyHelper::OFFICE_ID,
            'name' => OfficeRoomsOfficeAllyHelper::OFFICE_ROOM_NAME,
        ]);

        $checkingData = [
            'external_id' => $roomData['external_id'],
            'name' => $roomData['name']
        ];

        $this->assertDatabaseHas(self::TABLE_OFFICES_ROOMS, [
            'external_id' => $roomData['external_id'],
            'office_id' => OfficeRoomsOfficeAllyHelper::OFFICE_ID,
            'name' => OfficeRoomsOfficeAllyHelper::OFFICE_ROOM_NAME,
        ]);
        $this->assertDatabaseMissing(self::TABLE_OFFICES_ROOMS, $checkingData);

        $mockData = OfficeRoomsOfficeAllyHelper::getMockOfficeRoomsData($roomData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getOfficeRooms', $mockData);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper;
        });

        (new OfficeRoomsParser())->handleParser();

        $this->assertDatabaseHas(self::TABLE_OFFICES_ROOMS, $checkingData);
        $this->assertDatabaseMissing(self::TABLE_OFFICES_ROOMS, [
            'external_id' => $roomData['external_id'],
            'office_id' => OfficeRoomsOfficeAllyHelper::OFFICE_ID,
            'name' => OfficeRoomsOfficeAllyHelper::OFFICE_ROOM_NAME,
        ]);
    }
}

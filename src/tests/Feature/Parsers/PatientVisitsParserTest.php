<?php

namespace Tests\Feature\Parsers;

use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Jobs\DeleteVisits;
use App\Jobs\Parsers\Guzzle\PatientVisitsInfoParser;
use App\Jobs\Parsers\Guzzle\PatientVisitsParser;
use App\Jobs\Salary\CalculateOvertime;
use App\Option;
use App\PatientVisit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\OfficeAlly\VisitsOfficeAllyHelper;
use Tests\Traits\OfficeAlly\OfficeAllyTrait;

class PatientVisitsParserTest extends TestCase
{
    use OfficeAllyTrait;

    protected const OA_ACCOUNT = Option::OA_ACCOUNT_3;

    protected const ID_FOR_TEST = 1;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testOfficeAllyAuthorization()
    {
        Event::fake();

        self::authorizationTest($this, self::OA_ACCOUNT);
    }

    public function testVisitDataStructure()
    {
        Event::fake();

        $officeAllyHelper = new OfficeAllyHelper(self::OA_ACCOUNT);

        $dateRange = VisitsOfficeAllyHelper::getDateRange();

        $visits = $officeAllyHelper->getVisitListByDateRange($dateRange['start_date'], $dateRange['end_date']);

        $visitData = $visits[0];

        $this->assertArrayHasKey('id', $visitData, "The key 'id' is missing in the visit data.");

        foreach (VisitsOfficeAllyHelper::getCellStructureVisitData() as $key => $type) {
            $this->assertArrayHasKey($key, $visitData['cell'], "The key '$key' is missing in the visit data.");
            $this->assertInternalType($type, $visitData['cell'][$key], "The value of '$key' does not correspond to type '$type'.");
        }
    }

    public function testPatientVisitStatusCreate() {
        Event::fake();
        Bus::fake();

        $visitData = VisitsOfficeAllyHelper::getVisitData();

        $checkingData = ['name' => $visitData['status']];

        $this->assertDatabaseMissing(self::TABLE_PATIENT_VISIT_STATUSES, $checkingData);

        $mockData = VisitsOfficeAllyHelper::getMockVisitData($visitData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getVisitListByDateRange', $mockData);

        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper;
        });

        $options = VisitsOfficeAllyHelper::getDefaultOptions();

        (new PatientVisitsParser($options))->handleParser();

        $this->assertDatabaseHas(self::TABLE_PATIENT_VISIT_STATUSES, $checkingData);
    }

    public function testPatientVisitsCreate() {
        Event::fake();
        Bus::fake();

        $visitData = VisitsOfficeAllyHelper::getVisitData();

        $checkingData = [
            'visit_id'  => $visitData['visit_id'],
            'is_paid'   => $visitData['is_paid'],
            'date'      => Carbon::parse($visitData['date'])->toDateString(),
        ];

        $this->assertDatabaseMissing(self::TABLE_PATIENT_VISITS, $checkingData);

        $mockData = VisitsOfficeAllyHelper::getMockVisitData($visitData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getVisitListByDateRange', $mockData);

        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper;
        });

        $options = VisitsOfficeAllyHelper::getDefaultOptions();

        (new PatientVisitsParser($options))->handleParser();

        $this->assertDatabaseHas(self::TABLE_PATIENT_VISITS, $checkingData);
    }

    public function testRestoreTrashedPatientVisits() {
        Event::fake();
        Bus::fake();

        $visitData = VisitsOfficeAllyHelper::getVisitData();

        $visit = PatientVisit::create(['visit_id' => $visitData['visit_id']]);
        $visit->delete();

        $checkingData = [
            'visit_id'  => $visitData['visit_id'],
            'is_paid'   => $visitData['is_paid'],
            'date'      => Carbon::parse($visitData['date'])->toDateString(),
        ];

        $this->assertSoftDeleted(
            self::TABLE_PATIENT_VISITS,
            ['visit_id' => $visitData['visit_id']]
        );

        $mockData = VisitsOfficeAllyHelper::getMockVisitData($visitData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getVisitListByDateRange', $mockData);

        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper;
        });

        $options = VisitsOfficeAllyHelper::getDefaultOptions();

        (new PatientVisitsParser($options))->handleParser();

        $this->assertDatabaseHas(self::TABLE_PATIENT_VISITS, $checkingData);
    }

    public function testPatientVisitsUpdate() {
        Event::fake();
        Bus::fake();

        $visitData = VisitsOfficeAllyHelper::getVisitData();

        PatientVisit::create(['visit_id' => $visitData['visit_id']]);

        $checkingData = [
            'visit_id'  => $visitData['visit_id'],
            'is_paid'   => $visitData['is_paid'],
            'date'      => Carbon::parse($visitData['date'])->toDateString(),
        ];

        $this->assertDatabaseHas(self::TABLE_PATIENT_VISITS, ['visit_id' => $visitData['visit_id']]);
        $this->assertDatabaseMissing(self::TABLE_PATIENT_VISITS, $checkingData);

        $mockData = VisitsOfficeAllyHelper::getMockVisitData($visitData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getVisitListByDateRange', $mockData);

        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper;
        });

        $options = VisitsOfficeAllyHelper::getDefaultOptions();

        (new PatientVisitsParser($options))->handleParser();

        $this->assertDatabaseHas(self::TABLE_PATIENT_VISITS, $checkingData);
    }

    public function testPatientVisitsInfoParserIsDispatched()
    {
        Bus::fake();
        Event::fake();

        $visitData = VisitsOfficeAllyHelper::getVisitData();

        PatientVisit::create(['visit_id' => $visitData['visit_id']]);

        $checkingData = [
            'visit_id'  => $visitData['visit_id'],
            'is_paid'   => $visitData['is_paid'],
            'date'      => Carbon::parse($visitData['date'])->toDateString(),
        ];

        $this->assertDatabaseHas(self::TABLE_PATIENT_VISITS, ['visit_id' => $visitData['visit_id']]);
        $this->assertDatabaseMissing(self::TABLE_PATIENT_VISITS, $checkingData);

        $mockData = VisitsOfficeAllyHelper::getMockVisitData($visitData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getVisitListByDateRange', $mockData);

        $this->instance(OfficeAllyHelper::class, function (string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        $options = VisitsOfficeAllyHelper::getDefaultOptions();

        (new PatientVisitsParser($options))->handleParser();

        Bus::assertDispatched(PatientVisitsInfoParser::class);
    }

    public function testDeleteVisitsJobIsDispatched()
    {
        Bus::fake();
        Event::fake();

        $visitData = VisitsOfficeAllyHelper::getVisitData();

        $checkingData = [
            'visit_id'  => $visitData['visit_id'],
            'is_paid'   => $visitData['is_paid'],
            'date'      => Carbon::parse($visitData['date'])->toDateString(),
        ];

        $this->assertDatabaseMissing(self::TABLE_PATIENT_VISITS, $checkingData);

        $mockData = VisitsOfficeAllyHelper::getMockVisitData($visitData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getVisitListByDateRange', $mockData);

        $this->instance(OfficeAllyHelper::class, function (string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        $options = VisitsOfficeAllyHelper::getDefaultOptions();

        (new PatientVisitsParser($options))->handleParser();

        Bus::assertDispatched(DeleteVisits::class);
    }

    public function testCalculateOvertimeJobIsDispatched()
    {
        Bus::fake();
        Event::fake();

        $visitData = VisitsOfficeAllyHelper::getVisitData();

        $checkingData = [
            'visit_id'  => $visitData['visit_id'],
            'is_paid'   => $visitData['is_paid'],
            'date'      => Carbon::parse($visitData['date'])->toDateString(),
        ];

        $this->assertDatabaseMissing(self::TABLE_PATIENT_VISITS, $checkingData);

        $mockData = VisitsOfficeAllyHelper::getMockVisitData($visitData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getVisitListByDateRange', $mockData);

        $this->instance(OfficeAllyHelper::class, function (string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        $options = VisitsOfficeAllyHelper::getDefaultOptions();

        (new PatientVisitsParser($options))->handleParser();

        Bus::assertDispatched(CalculateOvertime::class);
    }
}

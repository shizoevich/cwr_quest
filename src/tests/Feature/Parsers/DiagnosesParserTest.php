<?php

namespace Tests\Feature\Parsers;

use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Jobs\Parsers\Guzzle\DiagnosesParser;
use App\Models\Diagnose;
use App\Option;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Tests\Traits\OfficeAlly\OfficeAllyTrait;
use Tests\Helpers\OfficeAlly\DiagnosesOfficeAllyHelper;

class DiagnosesParserTest extends TestCase
{
    use OfficeAllyTrait;

    protected const OA_ACCOUNT = Option::OA_ACCOUNT_1;

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

    public function testDiagnosisDataStructure()
    {
        Event::fake();

        $officeAllyHelper = new OfficeAllyHelper(self::OA_ACCOUNT);

        $diagnosisHtml = $officeAllyHelper->getDiagnosisList('F', 1, null, null);
        $diagnosisData = DiagnosesOfficeAllyHelper::getDiagnosisDataFromHtml($diagnosisHtml);

        foreach (DiagnosesOfficeAllyHelper::getStructureDiagnosisData() as $key => $type) {
            $this->assertArrayHasKey($key, $diagnosisData, "The key '$key' is missing in the diagnosis data.");
            $this->assertInternalType($type, $diagnosisData[$key], "The value of '$key' does not correspond to type '$type'.");
        }
    }

    public function testCreateDiagnosis()
    {
        Event::fake();

        $diagnosisData = DiagnosesOfficeAllyHelper::getDiagnosisDataForHtml();

        $checkingData = [
            'code' => $diagnosisData['code'],
            'hcc' => $diagnosisData['hcc'],
            'is_billable' => $diagnosisData['is_billable'],
            'terminated_at' => $diagnosisData['terminated_at']
        ];

        $this->assertDatabaseMissing(self::TABLE_DIAGNOSES, $checkingData);

        $diagnosisHtml = DiagnosesOfficeAllyHelper::mockGetDiagnosisListHtml($diagnosisData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getDiagnosisList', $diagnosisHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new DiagnosesParser())->handleParser();

        $this->assertDatabaseHas(self::TABLE_DIAGNOSES, $checkingData);
    }

    public function testUpdateDiagnosis()
    {
        Event::fake();

        $diagnosisData = DiagnosesOfficeAllyHelper::getDiagnosisDataForHtml();

        Diagnose::create(['code' => $diagnosisData['code']]);

        $checkingData = [
            'code' => $diagnosisData['code'],
            'hcc' => $diagnosisData['hcc'],
            'is_billable' => $diagnosisData['is_billable'],
            'terminated_at' => $diagnosisData['terminated_at']
        ];

        $this->assertDatabaseHas(self::TABLE_DIAGNOSES, ['code' => $diagnosisData['code']]);
        $this->assertDatabaseMissing(self::TABLE_DIAGNOSES, $checkingData);

        $diagnosisHtml = DiagnosesOfficeAllyHelper::mockGetDiagnosisListHtml($diagnosisData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getDiagnosisList', $diagnosisHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new DiagnosesParser())->handleParser();

        $this->assertDatabaseHas(self::TABLE_DIAGNOSES, $checkingData);
    }
}

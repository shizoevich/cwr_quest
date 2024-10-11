<?php

namespace Tests\Feature\Parsers;

use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Jobs\Parsers\Guzzle\InsurancesParser;
use App\PatientInsurance;
use App\Option;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Tests\Traits\OfficeAlly\OfficeAllyTrait;
use Tests\Helpers\OfficeAlly\InsurancesOfficeAllyHelper;

class InsurancesParserTest extends TestCase
{
    use OfficeAllyTrait;

    protected const OA_ACCOUNT = Option::OA_ACCOUNT_1;

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

        $insurancesHtml = $officeAllyHelper->getInsurances();
        $insuranceData = InsurancesOfficeAllyHelper::getInsuranceDataFromHtml($insurancesHtml);

        foreach (InsurancesOfficeAllyHelper::getStructureInsuranceData() as $key => $type) {
            $this->assertArrayHasKey($key, $insuranceData, "The key '$key' is missing in the insurance data.");
            if ($insuranceData[$key]) {
                $this->assertInternalType($type, $insuranceData[$key], "The value of '$key' does not correspond to type '$type'.");
            }
        }
    }

    public function testCreateInsurance()
    {
        Event::fake();

        $insuranceData = InsurancesOfficeAllyHelper::getInsuranceDataForHtml();

        $checkingData = [
            'external_id' => $insuranceData['external_id'],
            'insurance' => $insuranceData['insurance'],
            'address_line_1' => $insuranceData['address_line_1'],
            'city' => $insuranceData['city'],
            'state' => $insuranceData['state'],
            'zip' => $insuranceData['zip'],
        ];

        $this->assertDatabaseMissing(self::TABLE_PATIENT_INSURANCES, $checkingData);

        $insurancesHtml = InsurancesOfficeAllyHelper::mockGetInsurancesHtml($insuranceData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getInsurances', $insurancesHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new InsurancesParser())->handleParser();

        $this->assertDatabaseHas(self::TABLE_PATIENT_INSURANCES, $checkingData);
    }

    public function testUpdateInsurance()
    {
        Event::fake();

        $insuranceData = InsurancesOfficeAllyHelper::getInsuranceDataForHtml();

        PatientInsurance::create(['external_id' => $insuranceData['external_id']]);

        $checkingData = [
            'insurance' => $insuranceData['insurance'],
            'address_line_1' => $insuranceData['address_line_1'],
            'city' => $insuranceData['city'],
            'state' => $insuranceData['state'],
            'zip' => $insuranceData['zip'],
        ];

        $this->assertDatabaseHas(self::TABLE_PATIENT_INSURANCES, ['external_id' => $insuranceData['external_id']]);
        $this->assertDatabaseMissing(self::TABLE_PATIENT_INSURANCES, $checkingData);

        $insurancesHtml = InsurancesOfficeAllyHelper::mockGetInsurancesHtml($insuranceData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getInsurances', $insurancesHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new InsurancesParser())->handleParser();

        $this->assertDatabaseHas(self::TABLE_PATIENT_INSURANCES, $checkingData);
    }
}

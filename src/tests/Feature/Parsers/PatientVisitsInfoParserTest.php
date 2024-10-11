<?php

namespace Tests\Feature\Parsers;

use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Jobs\Parsers\Guzzle\PatientVisitsInfoParser;
use App\Jobs\Salary\SyncSalaryData;
use App\Models\Diagnose;
use App\Models\Patient\Visit\PatientVisitDiagnose;
use App\Option;
use App\Patient;
use App\PatientInsurance;
use App\PatientInsuranceProcedure;
use App\PatientVisit;
use App\Provider;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Tests\Traits\OfficeAlly\OfficeAllyTrait;
use Tests\Helpers\OfficeAlly\VisitInfoOfficeAllyHelper;

class PatientVisitsInfoParserTest extends TestCase
{
    use OfficeAllyTrait;

    protected const OA_ACCOUNT = Option::OA_ACCOUNT_2;

    protected const OA_TEST_ID = 195449292;

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

        $visitInfoHtml = $officeAllyHelper->getVisitInfo(self::OA_TEST_ID);

        $visitInfoData = VisitInfoOfficeAllyHelper::getVisitInfoDataFromHtml($visitInfoHtml);

        $visitInfoStructure = VisitInfoOfficeAllyHelper::getStructureVisitInfoData();
        foreach ($visitInfoStructure as $key => $type) {
            switch ($key) {
                case 'insurance_plan_name':
                    $insuranceName = $visitInfoData['insurance_name'];
                    $this->assertArrayHasKey($key, $visitInfoData, "The key '$key' is missing in the visit info data.");
                    if (trim($insuranceName) != '') {
                        $this->assertInternalType($type, $visitInfoData[$key], "The value of '$key' does not correspond to type '$type'.");
                    }
                    break;
                case 'billings':
                    $this->assertArrayHasKey($key, $visitInfoData, "The key '$key' is missing in the visit info data.");
                    if ($visitInfoData[$key]) {
                        $billings = VisitInfoOfficeAllyHelper::getBillingsFromCell($visitInfoData[$key]);
                    }

                    if (!is_null($billings)) {
                        foreach ($visitInfoStructure[$key] as $subKey => $subType) {
                            $this->assertInternalType($subType, $billings[$subKey], "The value of '$subKey' does not correspond to type '$subType'.");
                        }
                    }
                    break;
                default:
                    $this->assertArrayHasKey($key, $visitInfoData, "The key '$key' is missing in the visit info data.");
                    $this->assertInternalType($type, $visitInfoData[$key], "The value of '$key' does not correspond to type '$type'.");
            }
        }
    }

    public function testPatientVisitCreate() {
        Event::fake();
        Bus::fake();

        $visitInfoData = VisitInfoOfficeAllyHelper::getVisitInfoDataForHtml();

        $provider = Provider::create(['officeally_id' => $visitInfoData['provider_id']]);
        $patient = Patient::create(['patient_id' => $visitInfoData['patient_id']]);

        $checkingData = [
            'visit_id' => self::OA_TEST_ID,
            'provider_id' => $provider->id,
            'copay' => $visitInfoData['copay'],
            'patient_id' => $patient->id,
            'is_cash' => $visitInfoData['is_cash'],
        ];

        $this->assertDatabaseMissing(self::TABLE_PATIENT_VISITS, $checkingData);

        $mockData = VisitInfoOfficeAllyHelper::getMockVisitInfoHtml($visitInfoData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getVisitInfo', $mockData);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper;
        });

        (new PatientVisitsInfoParser([self::OA_TEST_ID]))->handleParser();

        $this->assertDatabaseHas(self::TABLE_PATIENT_VISITS, $checkingData);
    }

    public function testPatientVisitUpdate() {
        Event::fake();
        Bus::fake();

        $visitInfoData = VisitInfoOfficeAllyHelper::getVisitInfoDataForHtml();

        $provider = Provider::create(['officeally_id' => $visitInfoData['provider_id']]);
        $patient = Patient::create(['patient_id' => $visitInfoData['patient_id']]);
        PatientVisit::create(['visit_id' => self::OA_TEST_ID]);

        $checkingData = [
            'visit_id' => self::OA_TEST_ID,
            'provider_id' => $provider->id,
            'copay' => $visitInfoData['copay'],
            'patient_id' => $patient->id,
            'is_cash' => $visitInfoData['is_cash'],
        ];

        $this->assertDatabaseHas(self::TABLE_PATIENT_VISITS, ['visit_id' => self::OA_TEST_ID]);
        $this->assertDatabaseMissing(self::TABLE_PATIENT_VISITS, $checkingData);

        $mockData = VisitInfoOfficeAllyHelper::getMockVisitInfoHtml($visitInfoData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getVisitInfo', $mockData);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper;
        });

        (new PatientVisitsInfoParser([self::OA_TEST_ID]))->handleParser();

        $this->assertDatabaseHas(self::TABLE_PATIENT_VISITS, $checkingData);
    }

    public function testVisitReasonCreate() {
        Event::fake();
        Bus::fake();

        $visitInfoData = VisitInfoOfficeAllyHelper::getVisitInfoDataForHtml();

        Provider::create(['officeally_id' => $visitInfoData['provider_id']]);
        Patient::create(['patient_id' => $visitInfoData['patient_id']]);

        $checkingData = [
            'slug' => str_slug($visitInfoData['visit_reason']), 
            'title' => $visitInfoData['visit_reason']
        ];

        $this->assertDatabaseMissing(self::TABLE_VISIT_REASONS, $checkingData);

        $mockData = VisitInfoOfficeAllyHelper::getMockVisitInfoHtml($visitInfoData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getVisitInfo', $mockData);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper;
        });

        (new PatientVisitsInfoParser([self::OA_TEST_ID]))->handleParser();

        $this->assertDatabaseHas(self::TABLE_VISIT_REASONS, $checkingData);
    }

    public function testPatientInsuranceCreate() {
        Event::fake();
        Bus::fake();

        $visitInfoData = VisitInfoOfficeAllyHelper::getVisitInfoDataForHtml();

        Provider::create(['officeally_id' => $visitInfoData['provider_id']]);
        Patient::create(['patient_id' => $visitInfoData['patient_id']]);

        $checkingData = [
            'insurance' => $visitInfoData['insurance_name']
        ];

        $this->assertDatabaseMissing(self::TABLE_PATIENT_INSURANCES, $checkingData);

        $mockData = VisitInfoOfficeAllyHelper::getMockVisitInfoHtml($visitInfoData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getVisitInfo', $mockData);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper;
        });

        (new PatientVisitsInfoParser([self::OA_TEST_ID]))->handleParser();

        $this->assertDatabaseHas(self::TABLE_PATIENT_INSURANCES, $checkingData);
    }

    public function testPatientInsurancePlanCreate() {
        Event::fake();
        Bus::fake();

        $visitInfoData = VisitInfoOfficeAllyHelper::getVisitInfoDataForHtml();

        Provider::create(['officeally_id' => $visitInfoData['provider_id']]);
        Patient::create(['patient_id' => $visitInfoData['patient_id']]);
        $insurance = PatientInsurance::create(['insurance' => $visitInfoData['insurance_name']]);

        $checkingData = [
            'insurance_id' => $insurance->id,
            'name'         => $visitInfoData['insurance_plan_name']
        ];

        $this->assertDatabaseMissing(self::TABLE_PATIENT_INSURANCES_PLANS, $checkingData);

        $mockData = VisitInfoOfficeAllyHelper::getMockVisitInfoHtml($visitInfoData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getVisitInfo', $mockData);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper;
        });

        (new PatientVisitsInfoParser([self::OA_TEST_ID]))->handleParser();

        $this->assertDatabaseHas(self::TABLE_PATIENT_INSURANCES_PLANS, $checkingData);
    }

    public function testPatientInsuranceProcedureCreate() {
        Event::fake();
        Bus::fake();

        $visitInfoData = VisitInfoOfficeAllyHelper::getVisitInfoDataForHtml();

        Provider::create(['officeally_id' => $visitInfoData['provider_id']]);
        Patient::create(['patient_id' => $visitInfoData['patient_id']]);

        $checkingData = [
            'code' => $visitInfoData['billing_cpt'],
            'name' => $visitInfoData['billing_description'],
        ];

        $this->assertDatabaseMissing(self::TABLE_PATIENT_INSURANCES_PROCEDURES, $checkingData);

        $mockData = VisitInfoOfficeAllyHelper::getMockVisitInfoHtml($visitInfoData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getVisitInfo', $mockData);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper;
        });

        (new PatientVisitsInfoParser([self::OA_TEST_ID]))->handleParser();

        $this->assertDatabaseHas(self::TABLE_PATIENT_INSURANCES_PROCEDURES, $checkingData);
    }

    public function testPatientInsuranceProcedureUpdate() {
        Event::fake();
        Bus::fake();

        $visitInfoData = VisitInfoOfficeAllyHelper::getVisitInfoDataForHtml();

        Provider::create(['officeally_id' => $visitInfoData['provider_id']]);
        Patient::create(['patient_id' => $visitInfoData['patient_id']]);
        PatientInsuranceProcedure::create(['code' => $visitInfoData['billing_cpt']]);

        $checkingData = [
            'code' => $visitInfoData['billing_cpt'],
            'name' => $visitInfoData['billing_description'],
        ];

        $this->assertDatabaseHas(self::TABLE_PATIENT_INSURANCES_PROCEDURES, ['code' => $visitInfoData['billing_cpt']]);
        $this->assertDatabaseMissing(self::TABLE_PATIENT_INSURANCES_PROCEDURES, $checkingData);

        $mockData = VisitInfoOfficeAllyHelper::getMockVisitInfoHtml($visitInfoData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getVisitInfo', $mockData);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper;
        });

        (new PatientVisitsInfoParser([self::OA_TEST_ID]))->handleParser();

        $this->assertDatabaseHas(self::TABLE_PATIENT_INSURANCES_PROCEDURES, $checkingData);
    }

    public function testDiagnoseCreate()
    {
        Event::fake();
        Bus::fake();

        $visitInfoData = VisitInfoOfficeAllyHelper::getVisitInfoDataForHtml();

        Provider::create(['officeally_id' => $visitInfoData['provider_id']]);
        Patient::create(['patient_id' => $visitInfoData['patient_id']]);

        $checkingData = [
            'code' => $visitInfoData['diagnose_code'], 
            'description' => $visitInfoData['diagnose_description']
        ];

        $this->assertDatabaseMissing(self::TABLE_DIAGNOSES, $checkingData);

        $mockData = VisitInfoOfficeAllyHelper::getMockVisitInfoHtml($visitInfoData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getVisitInfo', $mockData);
        $this->instance(OfficeAllyHelper::class, function (string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        (new PatientVisitsInfoParser([self::OA_TEST_ID]))->handleParser();

        $this->assertDatabaseHas(self::TABLE_DIAGNOSES, $checkingData);
    }

    public function testPatientVisitDiagnoseCreate() {
        Event::fake();
        Bus::fake();

        $visitInfoData = VisitInfoOfficeAllyHelper::getVisitInfoDataForHtml();

        Provider::create(['officeally_id' => $visitInfoData['provider_id']]);
        Patient::create(['patient_id' => $visitInfoData['patient_id']]);
        $diagnose = Diagnose::create([
            'code' => $visitInfoData['diagnose_code'], '
            description' => $visitInfoData['diagnose_description']
        ]);
        
        $checkingData = ['visit_id' => self::OA_TEST_ID, 'diagnose_id' => $diagnose->id];

        $this->assertDatabaseMissing(self::TABLE_PATIENT_VISIT_DIAGNOSES, $checkingData);

        $mockData = VisitInfoOfficeAllyHelper::getMockVisitInfoHtml($visitInfoData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getVisitInfo', $mockData);
        $this->instance(OfficeAllyHelper::class, function (string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        (new PatientVisitsInfoParser([self::OA_TEST_ID]))->handleParser();

        $this->assertDatabaseMissing(self::TABLE_PATIENT_VISIT_DIAGNOSES, $checkingData);
    }

    public function testPatientVisitDiagnoseDelete() {
        Event::fake();
        Bus::fake();

        $visitInfoData = VisitInfoOfficeAllyHelper::getVisitInfoDataForHtml();

        Provider::create(['officeally_id' => $visitInfoData['provider_id']]);
        Patient::create(['patient_id' => $visitInfoData['patient_id']]);
        $patientVisit = PatientVisit::create(['visit_id' => self::OA_TEST_ID]);
        $diagnose = Diagnose::create();
        
        $checkingData = ['visit_id' => $patientVisit->id, 'diagnose_id' => $diagnose->id];
        
        PatientVisitDiagnose::create($checkingData);

        $this->assertDatabaseHas(self::TABLE_PATIENT_VISIT_DIAGNOSES, $checkingData);

        $mockData = VisitInfoOfficeAllyHelper::getMockVisitInfoHtml($visitInfoData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getVisitInfo', $mockData);
        $this->instance(OfficeAllyHelper::class, function (string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        (new PatientVisitsInfoParser([self::OA_TEST_ID]))->handleParser();

        $this->assertDatabaseMissing(self::TABLE_PATIENT_VISIT_DIAGNOSES, $checkingData);
    }

    public function testDispatchSyncSalaryData()
    {
        Event::fake();
        Bus::fake();

        $visitInfoData = VisitInfoOfficeAllyHelper::getVisitInfoDataForHtml();

        Provider::create(['officeally_id' => $visitInfoData['provider_id']]);
        Patient::create(['patient_id' => $visitInfoData['patient_id']]);

        $mockData = VisitInfoOfficeAllyHelper::getMockVisitInfoHtml($visitInfoData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getVisitInfo', $mockData);
        $this->instance(OfficeAllyHelper::class, function (string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        (new PatientVisitsInfoParser([self::OA_TEST_ID], true))->handleParser();

        Bus::assertDispatched(SyncSalaryData::class);
    }
}

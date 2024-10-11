<?php

namespace Tests\Feature\Parsers;

use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Jobs\Parsers\Guzzle\PatientProfilesParser;
use App\Models\Diagnose;
use App\Models\EligibilityPayer;
use App\Models\PatientHasProvider;
use App\Option;
use App\Patient;
use App\PatientInsurance;
use App\PatientInsurancePlan;
use App\Provider;
use Illuminate\Support\Facades\Event;
use Symfony\Component\DomCrawler\Crawler;
use Tests\Helpers\OfficeAlly\PatientProfileOfficeAllyHelper;
use Tests\TestCase;
use Tests\Traits\OfficeAlly\OfficeAllyTrait;
use Tests\Traits\PatientTrait;

class PatientProfilesParserTest extends TestCase
{
    use OfficeAllyTrait;
    use PatientTrait;

    protected const OA_ACCOUNT = Option::OA_ACCOUNT_3;

    public function testOfficeAllyAuthorization()
    {
        Event::fake();

        self::authorizationTest($this, self::OA_ACCOUNT);
    }

    public function testPatientProfileDataStructure()
    {
        Event::fake();

        $officeAllyHelper = new OfficeAllyHelper(self::OA_ACCOUNT);
        $patientProfileHtml = $officeAllyHelper->getPatientProfile(PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID);
        $crawler = new Crawler($patientProfileHtml);

        $patientData = PatientProfileOfficeAllyHelper::getPatientDataFromPatientProfileHtml($crawler);
        $providerData = PatientProfileOfficeAllyHelper::getProviderDataFromPatientProfileHtml($crawler);
        $diagnosesData = PatientProfileOfficeAllyHelper::getDiagnosesDataFromPatientProfileHtml($crawler);
        $templatesData = PatientProfileOfficeAllyHelper::getTemplatesDataFromPatientProfileHtml($crawler);
        $insuranceData = PatientProfileOfficeAllyHelper::getInsuranceDataFromPatientProfileHtml($crawler);

        $this->assertDataStructure($patientData, PatientProfileOfficeAllyHelper::getPatientStructureFromPatientProfile());
        $this->assertInternalType('string', $providerData, 'The providerData does not correspond to type string.');
        foreach ($diagnosesData as $diagnoseData) {
            $this->assertDataStructure($diagnoseData, PatientProfileOfficeAllyHelper::getDiagnoseStructureFromPatientProfile());
        }
        foreach ($templatesData as $templateData) {
            $this->assertDataStructure($templateData, PatientProfileOfficeAllyHelper::getTemplateStructureFromPatientProfile());
        }
        $this->assertDataStructure($insuranceData, PatientProfileOfficeAllyHelper::getInsuranceStructureFromPatientProfile());
    }


    public function testPatientProfileCreatePatient()
    {
        Event::fake();

        $this->assertDatabaseMissing(
            self::TABLE_PATIENTS,
            [
                'patient_id' => PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID,
            ]
        );

        $patientProfileHtml = PatientProfileOfficeAllyHelper::getPatientProfileHtmlMock(
            PatientProfileOfficeAllyHelper::getPatientDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getProviderDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getDiagnosisDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getTemplatesDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getInsuranceDataForPatientProfileHtmlMock()
        );

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getPatientProfile', $patientProfileHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        (new PatientProfilesParser([PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID], 0))->handleParser();

        $this->assertDatabaseHas(
            self::TABLE_PATIENTS,
            [
                'patient_id' => PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID,
            ]
        );
    }

    public function testPatientProfileUpdatePatient()
    {
        Event::fake();

        $firstName = '!test_first_name!';
        $lastName = '!test_last_name!';

        self::generatePatient([
            'patient_id' => PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);

        $this->assertDatabaseHas(
            self::TABLE_PATIENTS,
            [
                'patient_id' => PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]
        );

        $patientProfileHtml = PatientProfileOfficeAllyHelper::getPatientProfileHtmlMock(
            PatientProfileOfficeAllyHelper::getPatientDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getProviderDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getDiagnosisDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getTemplatesDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getInsuranceDataForPatientProfileHtmlMock()
        );

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getPatientProfile', $patientProfileHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        (new PatientProfilesParser([PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID], 0))->handleParser();

        $this->assertDatabaseMissing(
            self::TABLE_PATIENTS,
            [
                'patient_id' => PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]
        );

        $this->assertDatabaseHas(
            self::TABLE_PATIENTS,
            [
                'patient_id' => PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID,
                'first_name' => PatientProfileOfficeAllyHelper::OA_PATIENT_FIRST_NAME,
                'last_name' => PatientProfileOfficeAllyHelper::OA_PATIENT_LAST_NAME,
            ]
        );
    }

    public function testPatientProfileCreateProvider()
    {
        Event::fake();

        $this->assertDatabaseMissing(
            self::TABLE_PROVIDERS,
            [
                'provider_name' => PatientProfileOfficeAllyHelper::OA_PROVIDER_NAME,
            ]
        );

        $patientProfileHtml = PatientProfileOfficeAllyHelper::getPatientProfileHtmlMock(
            PatientProfileOfficeAllyHelper::getPatientDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getProviderDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getDiagnosisDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getTemplatesDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getInsuranceDataForPatientProfileHtmlMock()
        );

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getPatientProfile', $patientProfileHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        (new PatientProfilesParser([PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID], 0))->handleParser();

        $this->assertDatabaseHas(
            self::TABLE_PROVIDERS,
            [
                'provider_name' => PatientProfileOfficeAllyHelper::OA_PROVIDER_NAME,
            ]
        );

        $patientId = Patient::where('patient_id', PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID)->first()->id;
        $providerId = Provider::where('provider_name', PatientProfileOfficeAllyHelper::OA_PROVIDER_NAME)->first()->id;

        $this->assertDatabaseHas(
            self::TABLE_PATIENT_HAS_PROVIDERS,
            [
                'patients_id' => $patientId,
                'providers_id' => $providerId,
            ]
        );
    }

    public function testPatientProfileUpdatePatientHasProvider()
    {
        Event::fake();

        $patient = self::generatePatient([
            'patient_id' => PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID,
        ]);

        $provider = factory(Provider::class)->create([
            'provider_name' => PatientProfileOfficeAllyHelper::OA_PROVIDER_NAME,
        ]);

        PatientHasProvider::create([
            'patients_id' => $patient->id,
            'providers_id' => $provider->id,
            'chart_read_only' => 1,
        ]);

        $patientProfileHtml = PatientProfileOfficeAllyHelper::getPatientProfileHtmlMock(
            PatientProfileOfficeAllyHelper::getPatientDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getProviderDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getDiagnosisDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getTemplatesDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getInsuranceDataForPatientProfileHtmlMock()
        );

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getPatientProfile', $patientProfileHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        (new PatientProfilesParser([PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID], 0))->handleParser();

        $this->assertDatabaseHas(
            self::TABLE_PATIENT_HAS_PROVIDERS,
            [
                'patients_id' => $patient->id,
                'providers_id' => $provider->id,
                'chart_read_only' => 0,
            ]
        );
    }

    public function testPatientProfileCreateDiagnoses()
    {
        Event::fake();

        $this->assertDatabaseMissing(
            self::TABLE_DIAGNOSES,
            [
                'code' => PatientProfileOfficeAllyHelper::OA_DIAGNOSE_CODE,
                'description' => PatientProfileOfficeAllyHelper::OA_DIAGNOSE_DESCRIPTION,
            ]
        );

        $patientProfileHtml = PatientProfileOfficeAllyHelper::getPatientProfileHtmlMock(
            PatientProfileOfficeAllyHelper::getPatientDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getProviderDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getDiagnosisDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getTemplatesDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getInsuranceDataForPatientProfileHtmlMock()
        );

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getPatientProfile', $patientProfileHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        (new PatientProfilesParser([PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID], 0))->handleParser();

        $this->assertDatabaseHas(
            self::TABLE_DIAGNOSES,
            [
                'code' => PatientProfileOfficeAllyHelper::OA_DIAGNOSE_CODE,
                'description' => PatientProfileOfficeAllyHelper::OA_DIAGNOSE_DESCRIPTION,
            ]
        );
    }

    public function testPatientProfileSyncDiagnoses()
    {
        Event::fake();

        $testDiagnoseCode = '!test_code!';
        $testDiagnoseDescription = '!test_description!';

        $testDiagnose = Diagnose::create([
            'code' => $testDiagnoseCode,
            'description' => $testDiagnoseDescription,
        ]);

        $patient = self::generatePatient([
            'patient_id' => PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID,
        ]);

        $patient->diagnoses()->attach($testDiagnose);

        $this->assertDatabaseHas(
            self::TABLE_PATIENT_DIAGNOSES,
            [
                'patient_id' => $patient->id,
                'diagnose_id' => $testDiagnose->id,
            ]
        );

        $patientProfileHtml = PatientProfileOfficeAllyHelper::getPatientProfileHtmlMock(
            PatientProfileOfficeAllyHelper::getPatientDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getProviderDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getDiagnosisDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getTemplatesDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getInsuranceDataForPatientProfileHtmlMock()
        );

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getPatientProfile', $patientProfileHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        (new PatientProfilesParser([PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID], 0))->handleParser();

        $this->assertDatabaseMissing(
            self::TABLE_PATIENT_DIAGNOSES,
            [
                'patient_id' => $patient->id,
                'diagnose_id' => $testDiagnose->id,
            ]
        );

        $newDiagnose = Diagnose::where('code', PatientProfileOfficeAllyHelper::OA_DIAGNOSE_CODE)->first();

        $this->assertDatabaseHas(
            self::TABLE_PATIENT_DIAGNOSES,
            [
                'patient_id' => $patient->id,
                'diagnose_id' => $newDiagnose->id,
            ]
        );
    }

    public function testPatientProfileOldDiagnoses()
    {
        Event::fake();

        $testDiagnoseCode = '!test_code!';
        $testDiagnoseDescription = '!test_description!';

        $testDiagnose = Diagnose::create([
            'code' => $testDiagnoseCode,
            'description' => $testDiagnoseDescription,
        ]);

        $patient = self::generatePatient([
            'patient_id' => PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID,
        ]);

        $patient->diagnoses()->attach($testDiagnose);

        $patientProfileHtml = PatientProfileOfficeAllyHelper::getPatientProfileHtmlMock(
            PatientProfileOfficeAllyHelper::getPatientDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getProviderDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getDiagnosisDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getTemplatesDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getInsuranceDataForPatientProfileHtmlMock()
        );

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getPatientProfile', $patientProfileHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        (new PatientProfilesParser([PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID], 0))->handleParser();

        $this->assertDatabaseHas(
            self::TABLE_PATIENT_DIAGNOSES_OLD,
            [
                'patient_officeally_id' => $patient->patient_id,
                'diagnose' => '"' . PatientProfileOfficeAllyHelper::OA_DIAGNOSE_CODE . ' - ' . PatientProfileOfficeAllyHelper::OA_DIAGNOSE_DESCRIPTION . '"',
            ]
        );
    }

    public function testPatientProfileCreateTemplates()
    {
        Event::fake();

        $patient = self::generatePatient([
            'patient_id' => PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID,
        ]);

        $this->assertDatabaseMissing(
            self::TABLE_PATIENT_TEMPLATES,
            [
                'patient_id' => $patient->id,
                'position' => PatientProfileOfficeAllyHelper::OA_PATIENT_TEMPLATE_POSITION,
            ]
        );

        $patientProfileHtml = PatientProfileOfficeAllyHelper::getPatientProfileHtmlMock(
            PatientProfileOfficeAllyHelper::getPatientDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getProviderDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getDiagnosisDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getTemplatesDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getInsuranceDataForPatientProfileHtmlMock()
        );

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getPatientProfile', $patientProfileHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        (new PatientProfilesParser([PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID], 0))->handleParser();

        $this->assertDatabaseHas(
            self::TABLE_PATIENT_TEMPLATES,
            [
                'patient_id' => $patient->id,
                'position' => PatientProfileOfficeAllyHelper::OA_PATIENT_TEMPLATE_POSITION,
            ]
        );
    }

    public function testPatientProfileUpdateTemplates()
    {
        Event::fake();

        $testTemplateCharge = 11.11;

        $patient = self::generatePatient([
            'patient_id' => PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID,
        ]);

        $patient->templates()->create([
            'position' => PatientProfileOfficeAllyHelper::OA_PATIENT_TEMPLATE_POSITION,
            'charge' => $testTemplateCharge,
        ]);

        $this->assertDatabaseHas(
            self::TABLE_PATIENT_TEMPLATES,
            [
                'patient_id' => $patient->id,
                'position' => PatientProfileOfficeAllyHelper::OA_PATIENT_TEMPLATE_POSITION,
                'charge' => $testTemplateCharge,
            ]
        );

        $patientProfileHtml = PatientProfileOfficeAllyHelper::getPatientProfileHtmlMock(
            PatientProfileOfficeAllyHelper::getPatientDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getProviderDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getDiagnosisDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getTemplatesDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getInsuranceDataForPatientProfileHtmlMock()
        );

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getPatientProfile', $patientProfileHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        (new PatientProfilesParser([PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID], 0))->handleParser();

        $this->assertDatabaseMissing(
            self::TABLE_PATIENT_TEMPLATES,
            [
                'patient_id' => $patient->id,
                'position' => PatientProfileOfficeAllyHelper::OA_PATIENT_TEMPLATE_POSITION,
                'charge' => $testTemplateCharge,
            ]
        );

        $this->assertDatabaseHas(
            self::TABLE_PATIENT_TEMPLATES,
            [
                'patient_id' => $patient->id,
                'position' => PatientProfileOfficeAllyHelper::OA_PATIENT_TEMPLATE_POSITION,
                'charge' => PatientProfileOfficeAllyHelper::OA_PATIENT_TEMPLATE_CHARGE,
            ]
        );
    }

    public function testPatientProfileCreateInsurance()
    {
        Event::fake();

        $this->assertDatabaseMissing(
            self::TABLE_PATIENT_INSURANCES,
            [
                'insurance' => PatientProfileOfficeAllyHelper::OA_INSURANCE_NAME,
            ]
        );

        $patientProfileHtml = PatientProfileOfficeAllyHelper::getPatientProfileHtmlMock(
            PatientProfileOfficeAllyHelper::getPatientDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getProviderDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getDiagnosisDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getTemplatesDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getInsuranceDataForPatientProfileHtmlMock()
        );

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getPatientProfile', $patientProfileHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        (new PatientProfilesParser([PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID], 0))->handleParser();

        $this->assertDatabaseHas(
            self::TABLE_PATIENT_INSURANCES,
            [
                'insurance' => PatientProfileOfficeAllyHelper::OA_INSURANCE_NAME,
            ]
        );

        $this->assertDatabaseHas(
            self::TABLE_PATIENT_INSURANCES_PLANS,
            [
                'name' => PatientProfileOfficeAllyHelper::OA_INSURANCE_PLAN_NAME,
            ]
        );

        $insurance = PatientInsurance::where('insurance', PatientProfileOfficeAllyHelper::OA_INSURANCE_NAME)->first();
        $plan = PatientInsurancePlan::where('name', PatientProfileOfficeAllyHelper::OA_INSURANCE_PLAN_NAME)->first();

        $this->assertDatabaseHas(
            self::TABLE_PATIENTS,
            [
                'patient_id' => PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID,
                'primary_insurance_id' => $insurance->id,
                'primary_insurance' => $insurance->insurance,
                'insurance_plan_id' => $plan->id,
            ]
        );
    }

    public function testPatientProfileUpdateInsurance()
    {
        Event::fake();

        PatientInsurance::create([
            'insurance' => PatientProfileOfficeAllyHelper::OA_INSURANCE_NAME,
        ]);

        $this->assertDatabaseHas(
            self::TABLE_PATIENT_INSURANCES,
            [
                'insurance' => PatientProfileOfficeAllyHelper::OA_INSURANCE_NAME,
                'external_id' => null,
            ]
        );

        $patientProfileHtml = PatientProfileOfficeAllyHelper::getPatientProfileHtmlMock(
            PatientProfileOfficeAllyHelper::getPatientDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getProviderDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getDiagnosisDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getTemplatesDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getInsuranceDataForPatientProfileHtmlMock()
        );

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getPatientProfile', $patientProfileHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        (new PatientProfilesParser([PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID], 0))->handleParser();

        $this->assertDatabaseMissing(
            self::TABLE_PATIENT_INSURANCES,
            [
                'insurance' => PatientProfileOfficeAllyHelper::OA_INSURANCE_NAME,
                'external_id' => null,
            ]
        );

        $this->assertDatabaseHas(
            self::TABLE_PATIENT_INSURANCES,
            [
                'insurance' => PatientProfileOfficeAllyHelper::OA_INSURANCE_NAME,
                'external_id' => PatientProfileOfficeAllyHelper::OA_INSURANCE_EXTERNAL_ID,
            ]
        );
    }

    public function testPatientProfileParseEligibilityPayer()
    {
        Event::fake();

        $this->assertDatabaseMissing(
            self::TABLE_ELIGIBILITY_PAYERS,
            [
                'external_id' => PatientProfileOfficeAllyHelper::OA_ELIGIBILITY_PAYER_EXTERNAL_ID,
                'name' => PatientProfileOfficeAllyHelper::OA_ELIGIBILITY_PAYER_NAME,
            ]
        );

        $patientProfileHtml = PatientProfileOfficeAllyHelper::getPatientProfileHtmlMock(
            PatientProfileOfficeAllyHelper::getPatientDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getProviderDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getDiagnosisDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getTemplatesDataForPatientProfileHtmlMock(),
            PatientProfileOfficeAllyHelper::getInsuranceDataForPatientProfileHtmlMock()
        );

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getPatientProfile', $patientProfileHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) {
            return $officeAllyHelper;
        });

        (new PatientProfilesParser([PatientProfileOfficeAllyHelper::OA_PATIENT_PATIENT_ID], 0))->handleParser();

        $this->assertDatabaseHas(
            self::TABLE_ELIGIBILITY_PAYERS,
            [
                'external_id' => PatientProfileOfficeAllyHelper::OA_ELIGIBILITY_PAYER_EXTERNAL_ID,
                'name' => PatientProfileOfficeAllyHelper::OA_ELIGIBILITY_PAYER_NAME,
            ]
        );

        $eligibilityPayer = EligibilityPayer::where('external_id', PatientProfileOfficeAllyHelper::OA_ELIGIBILITY_PAYER_EXTERNAL_ID)->first();

        $this->assertDatabaseHas(
            self::TABLE_PATIENTS,
            [
                'eligibility_payer_id' => $eligibilityPayer->id,
            ]
        );
    }
}
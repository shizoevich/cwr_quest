<?php

namespace Tests\Feature\Parsers;

use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Jobs\Parsers\Guzzle\ProviderProfilesParser;
use App\Jobs\Parsers\Guzzle\ProvidersParser;
use App\Option;
use App\Patient;
use App\Provider;
use Illuminate\Support\Facades\Bus;
use Tests\Helpers\OfficeAlly\ProvidersOfficeAllyHelper;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Tests\Traits\OfficeAlly\OfficeAllyTrait;

class ProvidersParserTest extends TestCase
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

    public function testProviderDataStructure()
    {
        Event::fake();

        $officeAllyHelper = new OfficeAllyHelper(self::OA_ACCOUNT);

        $providers = $officeAllyHelper->getProviderList();
        $providerData = ProvidersOfficeAllyHelper::getProviderDataFromJson($providers);

        foreach (ProvidersOfficeAllyHelper::getStructureProviderData() as $key => $type) {
            $this->assertArrayHasKey($key, $providerData, "The key '$key' is missing in the provider data.");
            $this->assertInternalType($type, $providerData[$key], "The value of '$key' does not correspond to type '$type'.");
        }
    }

    public function testCreateProviders()
    {
        Event::fake();
        Bus::fake();

        Patient::create(['id' => 1111]);

        $providerData = ProvidersOfficeAllyHelper::getProviderDataForHtml();

        $checkingData = [
            'officeally_id' => $providerData['officeally_id'],
            'provider_name' => $providerData['first_name'] . ' ' . $providerData['last_name'],
            'phone'         => ProvidersOfficeAllyHelper::sanitizePhone($providerData['phone']),
        ];

        $this->assertDatabaseMissing(self::TABLE_PROVIDERS, $checkingData);

        $mockData = ProvidersOfficeAllyHelper::getMockProvidersData($providerData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getProviderList', $mockData);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper;
        });

        (new ProvidersParser())->handleParser();

        $this->assertDatabaseHas(self::TABLE_PROVIDERS, $checkingData);
    }

    public function testUpdateProviders()
    {
        Event::fake();
        Bus::fake();

        $providerData = ProvidersOfficeAllyHelper::getProviderDataForHtml();

        $provider = Provider::find(self::ID_FOR_TEST);
        $provider->officeally_id = $providerData['officeally_id'];
        $provider->phone = null;
        $provider->save();

        $checkingData = [
            'officeally_id' => $providerData['officeally_id'],
            'provider_name' => $providerData['first_name'] . ' ' . $providerData['last_name'],
            'phone'         => ProvidersOfficeAllyHelper::sanitizePhone($providerData['phone']),
        ];

        $this->assertDatabaseHas(self::TABLE_PROVIDERS, ['officeally_id' => $providerData['officeally_id']]);
        $this->assertDatabaseMissing(self::TABLE_PROVIDERS, $checkingData);

        $mockData = ProvidersOfficeAllyHelper::getMockProvidersData($providerData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getProviderList', $mockData);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper;
        });

        (new ProvidersParser())->handleParser();

        $this->assertDatabaseHas(self::TABLE_PROVIDERS, $checkingData);
    }

    public function testCreatePatientHasProviders()
    {
        Event::fake();
        Bus::fake();

        $patient = Patient::create(['id' => 1111]);

        $providerData = ProvidersOfficeAllyHelper::getProviderDataForHtml();

        $this->assertDatabaseMissing(self::TABLE_PATIENT_HAS_PROVIDERS, []);

        $mockData = ProvidersOfficeAllyHelper::getMockProvidersData($providerData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getProviderList', $mockData);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper;
        });

        (new ProvidersParser())->handleParser();

        $provider = Provider::where('officeally_id', $providerData['officeally_id'])->first();

        $this->assertDatabaseHas(self::TABLE_PATIENT_HAS_PROVIDERS, [
            'patients_id' => $patient->id,
            'providers_id' => $provider->id
        ]);
    }

    public function testProviderProfilesParserIsDispatched()
    {
        Bus::fake();
        Event::fake();

        Patient::create(['id' => 1111]);

        $providerData = ProvidersOfficeAllyHelper::getProviderDataForHtml();

        $mockData = ProvidersOfficeAllyHelper::getMockProvidersData($providerData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getProviderList', $mockData);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper;
        });

        (new ProvidersParser())->handleParser();

        Bus::assertDispatched(ProviderProfilesParser::class);
    }
}

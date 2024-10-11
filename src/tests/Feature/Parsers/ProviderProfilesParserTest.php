<?php

namespace Tests\Feature\Parsers;

use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Jobs\Parsers\Guzzle\ProviderProfilesParser;
use App\Option;
use App\Provider;
use Tests\Helpers\OfficeAlly\ProviderProfileOfficeAllyHelper;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Tests\Traits\OfficeAlly\OfficeAllyTrait;

class ProviderProfilesParserTest extends TestCase
{
    use OfficeAllyTrait;

    protected const OA_ACCOUNT = Option::OA_ACCOUNT_1;

    protected const ID_FOR_TEST = 1;

    protected const OA_TEST_ID = 290636;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testOfficeAllyAuthorization()
    {
        Event::fake();

        self::authorizationTest($this, self::OA_ACCOUNT);
    }

    public function testProviderProfileDataStructure()
    {
        Event::fake();

        $officeAllyHelper = new OfficeAllyHelper(self::OA_ACCOUNT);

        $providerProfileHtml = $officeAllyHelper->getProviderProfile(self::OA_TEST_ID);
        $providerProfileData = ProviderProfileOfficeAllyHelper::getProviderProfileDataFromHtml($providerProfileHtml);

        foreach (ProviderProfileOfficeAllyHelper::getStructureProviderProfileData() as $key => $type) {
            $this->assertArrayHasKey($key, $providerProfileData, "The key '$key' is missing in the provider profile data.");
            if ($providerProfileData[$key]) {
                $this->assertInternalType($type, $providerProfileData[$key], "The value of '$key' does not correspond to type '$type'.");
            }
        }
    }

    public function testProviderProfileUpdate() {
        Event::fake();

        $provider = Provider::find(self::ID_FOR_TEST);
        $provider->officeally_id = self::OA_TEST_ID;
        $provider->save();

        $providerProfileData = ProviderProfileOfficeAllyHelper::getProviderProfileDataForHtml();

        $checkingData = [
            'officeally_id' => self::OA_TEST_ID,
            'license_no' => $providerProfileData['license_no'],
            'individual_npi' => $providerProfileData['individual_npi']
        ];

        $this->assertDatabaseMissing(self::TABLE_PROVIDERS, $checkingData);

        $providerProfileHtml = ProviderProfileOfficeAllyHelper::getMockProviderProfileHtml($providerProfileData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getProviderProfile', $providerProfileHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper; 
        });

        (new ProviderProfilesParser([self::OA_TEST_ID]))->handleParser();

        $this->assertDatabaseHas(self::TABLE_PROVIDERS, $checkingData);
    }
}

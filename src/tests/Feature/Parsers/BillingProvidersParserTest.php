<?php

namespace Tests\Feature\Parsers;

use App\BillingProvider;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Jobs\Parsers\Guzzle\BillingProvidersParser;
use App\Option;
use Tests\Helpers\OfficeAlly\BillingProvidersOfficeAllyHelper;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Tests\Traits\OfficeAlly\OfficeAllyTrait;

class BillingProvidersParserTest extends TestCase
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

    public function testAppointmentDataStructure()
    {
        Event::fake();

        $officeAllyHelper = new OfficeAllyHelper(self::OA_ACCOUNT);

        $billingProvidersHtml = $officeAllyHelper->getBillingProviders();
        $billingProviderData = BillingProvidersOfficeAllyHelper::getBillingProviderDataFromHtml($billingProvidersHtml);

        foreach (BillingProvidersOfficeAllyHelper::getBillingProviderDataStructure() as $key => $type) {
            $this->assertArrayHasKey($key, $billingProviderData, "The key '$key' is missing in the billing provider data.");
            $this->assertInternalType($type, $billingProviderData[$key], "The value of '$key' does not correspond to type '$type'.");
        }
    }

    public function testCreateBillingProviders() {
        Event::fake();

        $billingProviderData = BillingProvidersOfficeAllyHelper::getBillingProviderDataForHtml();

        $checkingData = [
            'id' => $billingProviderData['id'],
            'name' => $billingProviderData['name'],
            'address' => $billingProviderData['address'],
            'city' => $billingProviderData['city'],
            'state' => $billingProviderData['state'],
            'zip' => $billingProviderData['zip'],
            'group_no' => $billingProviderData['group_no'],
            'tax_id' => $billingProviderData['tax_id'],
            'npi' => $billingProviderData['npi'],
            'phone' => $billingProviderData['phone']
        ];

        $this->assertDatabaseMissing(self::TABLE_BILLING_PROVIDERS, $checkingData);

        $billingProvidersHtml = BillingProvidersOfficeAllyHelper::getMockBillingProvidersHtml($billingProviderData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getBillingProviders', $billingProvidersHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper;
        });

        (new BillingProvidersParser())->handleParser();

        $this->assertDatabaseHas(self::TABLE_BILLING_PROVIDERS, $checkingData);
    }

    public function testUpdateBillingProviders() {
        Event::fake();

        $billingProviderData = BillingProvidersOfficeAllyHelper::getBillingProviderDataForHtml();

        BillingProvider::create(['id' => $billingProviderData['id']]);

        $checkingData = [
            'id' => $billingProviderData['id'],
            'name' => $billingProviderData['name'],
            'address' => $billingProviderData['address'],
            'city' => $billingProviderData['city'],
            'state' => $billingProviderData['state'],
            'zip' => $billingProviderData['zip'],
            'group_no' => $billingProviderData['group_no'],
            'tax_id' => $billingProviderData['tax_id'],
            'npi' => $billingProviderData['npi'],
            'phone' => $billingProviderData['phone']
        ];

        $this->assertDatabaseHas(self::TABLE_BILLING_PROVIDERS, ['id' => $billingProviderData['id']]);
        $this->assertDatabaseMissing(self::TABLE_BILLING_PROVIDERS, $checkingData);

        $billingProvidersHtml = BillingProvidersOfficeAllyHelper::getMockBillingProvidersHtml($billingProviderData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getBillingProviders', $billingProvidersHtml);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper;
        });

        (new BillingProvidersParser())->handleParser();

        $this->assertDatabaseHas(self::TABLE_BILLING_PROVIDERS, $checkingData);
    }
}

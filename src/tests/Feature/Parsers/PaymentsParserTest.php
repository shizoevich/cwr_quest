<?php

namespace Tests\Feature\Parsers;

use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Jobs\Parsers\Guzzle\PaymentsParser;
use App\Models\Officeally\OfficeallyTransaction;
use App\Option;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Tests\Traits\OfficeAlly\OfficeAllyTrait;
use Tests\Traits\PatientTrait;
use Tests\Traits\OfficeallyTransactionTypeTrait;
use Tests\Helpers\OfficeAlly\PaymentsOfficeAllyHelper;
use Carbon\Carbon;

class PaymentsParserTest extends TestCase
{
    use OfficeAllyTrait;
    use PatientTrait;
    use OfficeallyTransactionTypeTrait;

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

    public function testPaymentDataStructure()
    {
        Event::fake();

        $officeAllyHelper = new OfficeAllyHelper(self::OA_ACCOUNT);

        $payments = $officeAllyHelper->getPayments(PaymentsParser::PAYER_PATIENT, PaymentsParser::ALL_PAYMENTS);
        $paymentData = PaymentsOfficeAllyHelper::getPaymentDataFromJson($payments);
        
        $this->assertTrue($paymentData['transaction_date'] instanceof Carbon);

        foreach (PaymentsOfficeAllyHelper::getStructurePaymentData() as $key => $type) {
            $this->assertArrayHasKey($key, $paymentData, "The key '$key' is missing in the appointment data.");
            $this->assertInternalType($type, $paymentData[$key], "The value of '$key' does not correspond to type '$type'.");
        }
    }

    public function testCreatePayment()
    {
        Event::fake();

        $paymentData = PaymentsOfficeAllyHelper::getPaymentData();
        $patient = self::generatePatient([
            'patient_id' => PaymentsOfficeAllyHelper::PATIENT_ID,
        ]);
        $paymentData['patient_id'] = $patient->patient_id;

        $checkingData = [
            'external_id' => $paymentData['external_id'],
            'patient_id' => $patient->id,
            'payment_amount' => $paymentData['payment_amount'] * 100,
            'applied_amount' => $paymentData['applied_amount'] * 100,
            'transaction_date' => Carbon::createFromFormat('m/d/Y', $paymentData['transaction_date'])->toDateTimeString(),
        ];

        $this->assertDatabaseMissing(self::TABLE_OFFICEALLY_TRANSACTIONS, $checkingData);
        $this->assertDatabaseMissing(self::TABLE_OFFICEALLY_TRANSACTION_TYPES, [
            'name' => $paymentData['transaction_type']
        ]);

        $mockData = PaymentsOfficeAllyHelper::getMockPaymentsData($paymentData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getPayments', $mockData);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper;
        });

        (new PaymentsParser())->handleParser();

        $this->assertDatabaseHas(self::TABLE_OFFICEALLY_TRANSACTIONS, $checkingData);
        $this->assertDatabaseHas(self::TABLE_OFFICEALLY_TRANSACTION_TYPES, [
            'name' => $paymentData['transaction_type']
        ]);
    }

    public function testUpdatePayment()
    {
        Event::fake();

        $paymentData = PaymentsOfficeAllyHelper::getPaymentData();
        $patient = self::generatePatient([
            'patient_id' => PaymentsOfficeAllyHelper::PATIENT_ID,
        ]);
        $paymentData['patient_id'] = $patient->patient_id;
        $transactionType = self::generateOfficeallyTransactionType([
            'name' => $paymentData['transaction_type'],
        ]);

        OfficeallyTransaction::create([
            'external_id' => $paymentData['external_id'],
            'patient_id' => $patient->id,
            'transaction_type_id' => $transactionType->id,
            'payment_amount' => PaymentsOfficeAllyHelper::PAYMENT_AMOUNT,
            'applied_amount' => PaymentsOfficeAllyHelper::APPLIED_AMMOUNT,
        ]);

        $checkingData = [
            'external_id' => $paymentData['external_id'],
            'patient_id' => $patient->id,
            'transaction_type_id' => $transactionType->id,
            'payment_amount' => $paymentData['payment_amount'] * 100,
            'applied_amount' => $paymentData['applied_amount'] * 100,
            'transaction_date' => Carbon::createFromFormat('m/d/Y', $paymentData['transaction_date'])->toDateTimeString(),
        ];

        $this->assertDatabaseHas(self::TABLE_OFFICEALLY_TRANSACTIONS, [
            'external_id' => $paymentData['external_id'],
            'patient_id' => $patient->id,
            'transaction_type_id' => $transactionType->id,
            'payment_amount' => PaymentsOfficeAllyHelper::PAYMENT_AMOUNT,
            'applied_amount' => PaymentsOfficeAllyHelper::APPLIED_AMMOUNT,
        ]);
        $this->assertDatabaseMissing(self::TABLE_OFFICEALLY_TRANSACTIONS, $checkingData);

        $mockData = PaymentsOfficeAllyHelper::getMockPaymentsData($paymentData);

        $officeAllyHelper = self::getOfficeAllyHelperMock(self::OA_ACCOUNT, 'getPayments', $mockData);
        $this->instance(OfficeAllyHelper::class, function(string $accountName) use ($officeAllyHelper) { 
            return $officeAllyHelper;
        });

        (new PaymentsParser())->handleParser();

        $this->assertDatabaseHas(self::TABLE_OFFICEALLY_TRANSACTIONS, $checkingData);
        $this->assertDatabaseMissing(self::TABLE_OFFICEALLY_TRANSACTIONS, [
            'external_id' => $paymentData['external_id'],
            'patient_id' => $patient->id,
            'transaction_type_id' => $transactionType->id,
            'payment_amount' => PaymentsOfficeAllyHelper::PAYMENT_AMOUNT,
            'applied_amount' => PaymentsOfficeAllyHelper::APPLIED_AMMOUNT,
        ]);
    }
}

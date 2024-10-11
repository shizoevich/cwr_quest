<?php

namespace App\Jobs\Parsers\Guzzle;

use App\DTO\OfficeAlly\PaymentDTO;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Models\Officeally\OfficeallyTransaction;
use App\Models\Officeally\OfficeallyTransactionType;
use App\Option;
use App\Patient;
use Carbon\Carbon;
use App\Helpers\ExceptionNotificator;
use App\Models\Officeally\OfficeallyTransactionPurpose;
use App\Notifications\AnErrorOccurred;

class PaymentsParser extends AbstractParser
{
    const PAYER_ALL = 0;
    const PAYER_INSURANCE = 1;
    const PAYER_PATIENT = 2;
    const ALL_PAYMENTS = null;
    const APPLIED_PAYMENTS = 'A';
    const UNAPPLIED_PAYMENTS = 'U';
    /**
     * @var int
     */
    private $payerTypeId;
    private $paymentType;
    /**
     * @var null
     */
    private $dateFrom;
    /**
     * @var null
     */
    private $dateTo;

    /**
     * PaymentParser constructor.
     *
     * @param $dateFrom
     * @param $dateTo
     * @param int $payerTypeId
     * @param null $paymentType
     */
    public function __construct(Carbon $dateFrom = null, Carbon $dateTo = null, $payerTypeId = self::PAYER_PATIENT, $paymentType = self::ALL_PAYMENTS)
    {
        $this->paymentType = $paymentType;
        $this->payerTypeId = $payerTypeId;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handleParser()
    {
        $officeAllyHelper = app()->make(OfficeAllyHelper::class)(Option::OA_ACCOUNT_2);
        $payments = $officeAllyHelper->getPayments($this->payerTypeId, $this->paymentType, $this->dateFrom, $this->dateTo);
        if ($payments === null) {
            return;
        }
        $savedPayments = 0;

        foreach ($payments as $payment) {
            $patientId = $this->getPatientId($payment['cell'][15]);
            if (!$patientId) {
                continue;
            }

            $paymentDataDTO = new PaymentDTO([
                'external_id' => (string) $payment['id'],
                'patient_id' => $patientId,
                'transaction_type_id' => $this->getTransactionTypeId($payment['cell'][4]),
                'transaction_purpose_id' => $this->getTransactionPurposeId($payment['cell'][5]),
                'payment_amount' =>(float) $payment['cell'][8] * 100,
                'applied_amount' =>(float) $payment['cell'][9] * 100,
                'transaction_date' => Carbon::createFromFormat('m/d/Y', $payment['cell'][1]),
            ]);

            $paymentData = $paymentDataDTO->toArray();

            OfficeallyTransaction::updateOrCreate(array_only($paymentData, 'external_id'), $paymentData);

            $savedPayments++;
        }

        if ($savedPayments < count($payments)) {
            with(new ExceptionNotificator())->officeAllyNotifyAndSendToSentry(new AnErrorOccurred(sprintf(
                'Parsed %d payments, but saved %d',
                count($payments),
                $savedPayments
            )));
        }
    }

    /**
     * @param $patientId
     *
     * @return mixed|null
     */
    private function getPatientId($patientId)
    {
        return optional(Patient::query()->where('patient_id', $patientId)->first())->id;
    }

    /**
     * @param $type
     *
     * @return mixed
     */
    private function getTransactionTypeId($type)
    {
        return OfficeallyTransactionType::firstOrCreate([
            'name' => $type,
        ])->id;
    }

     /**
     * @param $name
     *
     * @return mixed
     */
    private function getTransactionPurposeId(string $name)
    {
        return OfficeallyTransactionPurpose::firstOrCreate([
            'description' => $name,
        ])->id;
    }
}

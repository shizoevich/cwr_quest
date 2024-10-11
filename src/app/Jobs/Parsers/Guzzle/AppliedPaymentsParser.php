<?php

namespace App\Jobs\Parsers\Guzzle;

use App\DTO\OfficeAlly\AppliedPaymentDTO;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Models\Officeally\OfficeallyAppliedTransaction;
use App\Models\Officeally\OfficeallyAppliedTransactionType;
use App\Option;
use App\PatientVisit;
use Carbon\Carbon;
use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;

class AppliedPaymentsParser extends AbstractParser
{
    const ANY_APPLIED_PAYMENT = '';
    const APPLIED_PAYMENTS = 'P';
    const APPLIED_ADJUSTMENTS = 'A';

    /**
     * @var int
     */
    private $appliedType;
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
     * @param Carbon $dateFrom
     * @param Carbon $dateTo
     * @param int $payerTypeId
     * @param null $appliedType
     */
    public function __construct(Carbon $dateFrom = null, Carbon $dateTo = null, $appliedType = self::ANY_APPLIED_PAYMENT)
    {
        $this->appliedType = $appliedType;
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
        $payments = $officeAllyHelper->getAppliedPayments($this->appliedType, $this->dateFrom, $this->dateTo);
        if ($payments === null) {
            return;
        }
        $savedPayments = 0;
        foreach ($payments as $payment) {
            $visitId = $this->getVisitId($payment['cell'][10]);
            if(!$visitId) {
                continue;
            }
            $paymentData = [ 
                'external_id' => $payment['id'],
                'applied_transaction_type_id' => $this->getTransactionTypeId($payment['cell'][12]),
                'patient_visit_id' => $visitId,
                'applied_amount' => abs($payment['cell'][13]),
                'applied_date' => Carbon::createFromFormat('m/d/Y', $payment['cell'][4]),
                'transaction_date' => isset($payment['cell'][5]) ? Carbon::createFromFormat('m/d/Y', $payment['cell'][5]) : null,
            ];

            // $paymentDataDTO  = new AppliedPaymentDTO([
            //    'external_id' => $payment['id'],
            //     'applied_transaction_type_id' => $this->getTransactionTypeId($payment['cell'][12]),
            //     'patient_visit_id' => $visitId,
            //     'applied_amount' => abs($payment['cell'][13]),
            //     'applied_date' => Carbon::createFromFormat('m/d/Y', $payment['cell'][4]),
            //     'transaction_date' => isset($payment['cell'][5]) ? Carbon::createFromFormat('m/d/Y', $payment['cell'][5]) : null,
            // ]);

            //  $paymentData = [ 
            //     'external_id' => $paymentDataDTO->external_id,
            //     'applied_transaction_type_id' => $paymentDataDTO->applied_transaction_type_id,
            //     'patient_visit_id' => $paymentDataDTO->patient_visit_id,
            //     'applied_amount' => $paymentDataDTO->applied_amount,
            //     'applied_date' => $paymentDataDTO->applied_date,
            //     'transaction_date' => $paymentDataDTO->transaction_date,
            // ];
    
            OfficeallyAppliedTransaction::updateOrCreate(array_only($paymentData, 'external_id'), $paymentData);
            $savedPayments++;
        }
    
        if ($savedPayments < count($payments)) {
            with(new ExceptionNotificator())->officeAllyNotifyAndSendToSentry(new AnErrorOccurred(sprintf('Parsed %d applied payments, but saved %d',
                count($payments), $savedPayments)));
        }
    }
    
    /**
     * @param $visitId
     *
     * @return mixed|null
     */
    private function getVisitId($visitId)
    {
        return optional(PatientVisit::query()->where('visit_id', $visitId)->first())->id;
    }
    
    /**
     * @param $type
     *
     * @return mixed
     */
    private function getTransactionTypeId($type)
    {
        return OfficeallyAppliedTransactionType::firstOrCreate([
            'name' => $type,
        ])->id;
    }
}

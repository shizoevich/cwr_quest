<?php

namespace App\Repositories\Appointment\Payment;

use App\Appointment;
use App\Exceptions\Officeally\Appointment\PaymentNotAddedException;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Jobs\Officeally\Retry\RetryAddPaymentToAppointment;
use App\Models\Square\SquarePaymentMethod;
use App\Option;
use App\Patient;
use Carbon\Carbon;
use Exception;

/**
 * Class AbstractPaymentRepository
 * @package App\Repositories\Appointment\Payment
 */
abstract class AbstractPaymentRepository
{
    /**
     * @var SquarePaymentMethod
     */
    private $paymentMethod;
    
    /**
     * @var array
     */
    protected $payload;
    
    /**
     * @var Patient
     */
    protected $patient;
    
    /**
     * @var Appointment
     */
    protected $appointment;
    
    /**
     * AbstractPaymentRepository constructor.
     *
     * @param SquarePaymentMethod $paymentMethod
     * @param Patient             $patient
     * @param Appointment         $appointment
     * @param array               $payload
     */
    public function __construct(SquarePaymentMethod $paymentMethod, Patient $patient, Appointment $appointment, array $payload)
    {
        $this->paymentMethod = $paymentMethod;
        $this->payload = $payload;
        $this->patient = $patient;
        $this->appointment = $appointment;
    }
    
    /**
     * @return bool
     * @throws PaymentNotAddedException
     */
    public function addPaymentToOfficeAlly(): bool
    {
        $account = Option::OA_ACCOUNT_2;
        $officeAllyHelper = new OfficeAllyHelper($account);

        $amount = $this->payload['amount'] / 100;
        $checkNo = $this->payload['check_no'] ?? '';
        $paymentMethod = $this->getOfficeAllyPaymentMethod();
        $description = $this->getOfficeAllyPaymentDescription();
        
        $delaySeconds = config('parser.job_retry_backoff_intervals')[0];

        if ($this->appointment->idAppointments) {
            try {
                $officeAllyHelper->addPaymentToAppointment($amount, $this->appointment->idAppointments, $this->patient->patient_id, $this->appointment->provider->officeally_id, $this->appointment->office->external_id, $paymentMethod, $description, $checkNo);
            } catch (Exception $e) {
                $job = (new RetryAddPaymentToAppointment($this->appointment->id, null, $account, $amount, $paymentMethod, $description, (string)$checkNo))->delay(Carbon::now()->addSeconds($delaySeconds));
                dispatch($job);
            }
        } else {
            $job = (new RetryAddPaymentToAppointment($this->appointment->id, null, $account, $amount, $paymentMethod, $description, (string)$checkNo))->delay(Carbon::now()->addSeconds($delaySeconds));
            dispatch($job);
        }

        return true;
    }
    
    /**
     * @return SquarePaymentMethod
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }
    
    protected function getOfficeAllyPaymentDescription()
    {
        return 'Copay';
    }
    
    /**
     * @return bool
     */
    abstract public function pay(): bool;
    
    /**
     * @return int
     */
    abstract protected function getOfficeAllyPaymentMethod(): int;
}
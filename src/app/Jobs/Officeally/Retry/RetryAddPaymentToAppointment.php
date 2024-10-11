<?php

namespace App\Jobs\Officeally\Retry;

use App\Appointment;
use App\Models\Officeally\OfficeallyTransaction;
use App\Exceptions\Officeally\OfficeallyAuthenticationException;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RetryAddPaymentToAppointment extends RetryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var float */
    private $amount;

    /** @var string */
    private $paymentMethod;

    /** @var string */
    private $description;

    /** @var string */
    private $checkNo;

    /** @var int */
    private $appointmentId;

    /** @var int */
    private $transactionId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        int $appointmentId,
        ?int $transactionId,
        string $officeAllyAccount,
        float $amount,
        string $paymentMethod,
        string $description = 'Copay',
        string $checkNo = ''
    ) {
        parent::__construct($officeAllyAccount);
        $this->amount = $amount;
        $this->paymentMethod = $paymentMethod;
        $this->description = $description;
        $this->checkNo = $checkNo;
        $this->appointmentId = $appointmentId;
        $this->transactionId = $transactionId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $appointment = Appointment::withTrashed()->find($this->appointmentId);

            if (!$appointment) {
                return;
            }
            if (!$appointment->idAppointments) {
                $this->handleRetry();

                return;
            }

            $appointmentId = $appointment->idAppointments;
            $patientId = $appointment->patient->patient_id;
            $providerId = $appointment->provider()->withTrashed()->first()->officeally_id;
            $officeId = $appointment->office->external_id;

            $officeAllyHelper = new OfficeAllyHelper($this->officeAllyAccount);
            $payment = $officeAllyHelper->addPaymentToAppointment($this->amount, $appointmentId, $patientId, $providerId, $officeId, $this->paymentMethod, $this->description, $this->checkNo);

            if (empty($payment)) {
                return;
            }

            $appointment->payed = true;
            $appointment->save();

            if (!$this->transactionId) {
                return;
            }

            $transaction = OfficeallyTransaction::find($this->transactionId);
            if (!$transaction) {
                return;
            }

            $transaction->update([
                'external_id' => $payment['id'],
                'transaction_date' => Carbon::createFromFormat('m/d/Y', $payment['cell'][1])
            ]);
        } catch (OfficeallyAuthenticationException $e) {
            $this->handleRetry();
        }
    }
}

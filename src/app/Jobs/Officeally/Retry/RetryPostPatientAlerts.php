<?php

namespace App\Jobs\Officeally\Retry;

use App\Exceptions\Officeally\OfficeallyAuthenticationException;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RetryPostPatientAlerts extends RetryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var mixed */
    private $alertMessage;

    /** @var int */
    private $patientId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $officeAllyAccount, string $alertMessage, int $patientId)
    {
        parent::__construct($officeAllyAccount);
        $this->alertMessage = $alertMessage;
        $this->patientId = $patientId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $patient = Patient::find($this->patientId);

            if (!$patient) {
                return;
            }
            if (!$patient->patient_id) {
                $this->handleRetry();

                return;
            }

            $officeAllyHelper = new OfficeAllyHelper($this->officeAllyAccount);
            $officeAllyHelper->postPatientAlerts($patient->patient_id, $this->alertMessage);

            dispatch(new RetrySyncPatientAlerts($this->officeAllyAccount, $patient->id));
        } catch (OfficeallyAuthenticationException $e) {
            $this->handleRetry();
        }
    }
}

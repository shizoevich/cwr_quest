<?php

namespace App\Jobs\Officeally\Retry;

use App\Exceptions\Officeally\OfficeallyAuthenticationException;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Option;
use App\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RetryDeleteUpcomingAppointments extends RetryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    private $patientId;

    /**
     * @var int|null
     */
    private $providerId;

    /**
     * Create a new job instance.
     *
     * @param int $patientId
     */
    public function __construct(int $patientId, string $officeAllyAccount = Option::OA_ACCOUNT_3,  int $providerId = null)
    {
        parent::__construct($officeAllyAccount);
        $this->patientId = $patientId;
        $this->providerId = $providerId;
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
            $officeAllyHelper->deleteUpcomingAppointments($patient->patient_id, $this->providerId ?? null);
        } catch (OfficeallyAuthenticationException $e) {
            $this->handleRetry();
        }
    }
}

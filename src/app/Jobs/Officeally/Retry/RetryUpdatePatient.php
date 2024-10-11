<?php

namespace App\Jobs\Officeally\Retry;

use App\Exceptions\Officeally\OfficeallyAuthenticationException;
use App\Helpers\RetryJobQueueHelper;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class RetryUpdatePatient extends RetryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var array */
    public $data;

    /** @var int */
    public $patientId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $officeAllyAccount, array $data, int $patientId)
    {
        parent::__construct($officeAllyAccount);
        $this->data = $data;
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
            $officeAllyHelper->updatePatient($patient->patient_id, $this->data);
        } catch (OfficeallyAuthenticationException $e) {
            $this->handleRetry();
        }
    }
}

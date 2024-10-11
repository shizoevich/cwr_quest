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

class RetryCreatePatient extends RetryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var array */
    private $data;

    /** @var int */
    private $patientId;

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
            $officeAllyHelper = new OfficeAllyHelper($this->officeAllyAccount);
            $patientId = $officeAllyHelper->createPatient($this->data);

            if (!$patientId) {
                return;
            }

            if (array_key_exists('diagnoses', $this->data) && $this->data['diagnoses']) {
                $dataForUpdate = ['diagnoses' => $this->data['diagnoses']];
                RetryJobQueueHelper::dispatchRetryUpdatePatient($this->officeAllyAccount, $dataForUpdate, $this->patientId);
            }

            $patient = Patient::find($this->patientId);

            if (!$patient) {
                return;
            }

            $patient->update(['patient_id' => (int)$patientId]);
        } catch (OfficeallyAuthenticationException $e) {
            $this->handleRetry();
        }
    }
}

<?php

namespace App\Jobs\Guzzle;

use App\Exceptions\Officeally\OfficeallyAuthenticationException;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Jobs\Officeally\Retry\RetryPostPatientAlerts;
use App\Jobs\Officeally\Retry\RetrySyncPatientAlerts;
use App\Option;
use App\Patient;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PostPatientAlerts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $storePatientAlertData;

    /**
     * PatientAlertsParser constructor.
     *
     * @param array $patientIds
     */
    public function __construct($storePatientAlertData)
    {
        $this->storePatientAlertData = $storePatientAlertData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $account = Option::OA_ACCOUNT_1;
        $officeAllyHelper = new OfficeAllyHelper($account);

        $patient = Patient::find($this->storePatientAlertData['patient_id']);
        $alertMessage = $this->storePatientAlertData['message'];

        $delaySeconds = config('parser.job_retry_backoff_intervals')[0];

        if ($patient->patient_id) {
            try {
                $officeAllyHelper->postPatientAlerts($patient->patient_id, $alertMessage);
                
                \Bus::dispatchNow(new RetrySyncPatientAlerts($account, $patient->id));
            } catch (OfficeallyAuthenticationException $e) {
                $job = (new RetryPostPatientAlerts($account, $alertMessage, $patient->id))->delay(Carbon::now()->addSeconds($delaySeconds));
                dispatch($job);
            }
        } else {
            $job = (new RetryPostPatientAlerts($account, $alertMessage, $patient->id))->delay(Carbon::now()->addSeconds($delaySeconds));
            dispatch($job);
        }
    }
}

<?php

namespace App\Jobs\Officeally\Retry;

use App\DTO\OfficeAlly\PatientAlertDTO;
use App\Exceptions\Officeally\OfficeallyAuthenticationException;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Jobs\Patients\SyncPatientAlert;
use App\Patient;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RetrySyncPatientAlerts extends RetryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int */
    private $patientId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $officeAllyAccount, int $patientId)
    {
        parent::__construct($officeAllyAccount);
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
            $alerts = $officeAllyHelper->getPatientAlerts($patient->patient_id);

            if (!$alerts) {
                return;
            }

            foreach ($alerts as $item) {
                $alertDTO = new PatientAlertDTO([
                    'officeally_alert_id' => intval($item['cell'][0]),
                    'patient_id' => $patient->id,
                    'date_created' => Carbon::parse($item['cell'][3])->toDateString(),
                    'message' => $item['cell'][4],
                    'resolved_by' => $item['cell'][5],
                    'status' => (string) $item['cell'][8],
                ]);

                if (!empty($item['cell'][6])) {
                    $alertDTO->date_resolved = Carbon::parse($item['cell'][6]);
                }

                \Bus::dispatchNow(new SyncPatientAlert($alertDTO));
            }
        } catch (OfficeallyAuthenticationException $e) {
            $this->handleRetry();
        }
    }
}

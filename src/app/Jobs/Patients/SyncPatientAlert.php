<?php

namespace App\Jobs\Patients;

use App\DTO\OfficeAlly\PatientAlertDTO;
use App\PatientAlert;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncPatientAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var PatientAlertDTO
     */
    private $patieltAlertDto;

    /**
     * Create a new job instance.
     *
     * @param Carbon $endDate
     * @param int|null $patientId
     */
    public function __construct(PatientAlertDTO $patieltAlertDto)
    {
        $this->patieltAlertDto = $patieltAlertDto;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $patientAlert = PatientAlert::where('officeally_alert_id', $this->patieltAlertDto->officeally_alert_id)->first();

        if (empty($patientAlert)) {
            $patientAlert = PatientAlert::where('patient_id', $this->patieltAlertDto->patient_id)
                ->whereDate('date_created', $this->patieltAlertDto->date_created)
                ->where('message', $this->patieltAlertDto->message)
                ->first();
        }

        if (empty($patientAlert)) {
            PatientAlert::create([
                'officeally_alert_id' => $this->patieltAlertDto->officeally_alert_id,
                'patient_id' => $this->patieltAlertDto->patient_id,
                'date_created' => $this->patieltAlertDto->date_created,
                'message' => $this->patieltAlertDto->message,
                'resolved_by' => $this->patieltAlertDto->resolved_by,
                'status' => $this->patieltAlertDto->status,
                'date_resolved' => $this->patieltAlertDto->date_resolved,
            ]);
        } else {
            $patientAlert->update([
                'officeally_alert_id' => $this->patieltAlertDto->officeally_alert_id,
                'patient_id' => $this->patieltAlertDto->patient_id,
                'date_created' => $this->patieltAlertDto->date_created,
                'message' => $this->patieltAlertDto->message,
                'resolved_by' => $this->patieltAlertDto->resolved_by,
                'status' => $this->patieltAlertDto->status,
                'date_resolved' => $this->patieltAlertDto->date_resolved,
            ]);
        }
    }
}

<?php

namespace App\Jobs\Officeally\Retry;

use App\Patient;
use App\Appointment;
use App\Exceptions\Officeally\OfficeallyAuthenticationException;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RetryCreateAppointment extends RetryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /** @var \App\DTO\OfficeAlly\Appointment */
    private $data;

    /** @var int */
    private $appointmentId;

    /** @var int */
    private $patientId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $officeAllyAccount, \App\DTO\OfficeAlly\Appointment $data, int $appointmentId, int $patientId)
    {
        parent::__construct($officeAllyAccount);
        $this->data = $data;
        $this->appointmentId = $appointmentId;
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

            $this->data->patientId = (int)$patient->patient_id;

            $officeAllyHelper = new OfficeAllyHelper($this->officeAllyAccount);
            $appointmentId = $officeAllyHelper->createAppointment($this->data);

            if (!$appointmentId) {
                return;
            }

            $appointment = Appointment::withTrashed()->find($this->appointmentId);
                
            if (!$appointment) {
                return;
            }
            
            $appointment->update(['idAppointments' => (int)$appointmentId]);
        } catch (OfficeallyAuthenticationException $e) {
            $this->handleRetry();
        }
    }
}

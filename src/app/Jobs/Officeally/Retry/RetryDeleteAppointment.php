<?php

namespace App\Jobs\Officeally\Retry;

use App\Appointment;
use App\Exceptions\Officeally\OfficeallyAuthenticationException;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RetryDeleteAppointment extends RetryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int */
    public $appointmentId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $officeAllyAccount, int $appointmentId)
    {
        parent::__construct($officeAllyAccount);
        $this->appointmentId = $appointmentId;
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

            $officeAllyHelper = new OfficeAllyHelper($this->officeAllyAccount);
            $officeAllyHelper->deleteAppointment($appointment->idAppointments);
        } catch (OfficeallyAuthenticationException $e) {
            $this->handleRetry();
        }
    }
}

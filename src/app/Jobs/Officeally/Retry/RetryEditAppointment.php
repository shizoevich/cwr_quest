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

class RetryEditAppointment extends RetryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var array */
    public $data;

    /** @var int */
    public $appointmentId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $officeAllyAccount, array $data, int $appointmentId)
    {
        parent::__construct($officeAllyAccount);
        $this->data = $data;
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

            $this->data['id'] = $appointment->idAppointments;

            $dataForUpdate = new \App\DTO\OfficeAlly\Appointment($this->data);

            $officeAllyHelper = new OfficeAllyHelper($this->officeAllyAccount);
            $officeAllyHelper->editAppointment($dataForUpdate);
        } catch (OfficeallyAuthenticationException $e) {
            $this->handleRetry();
        }
    }
}

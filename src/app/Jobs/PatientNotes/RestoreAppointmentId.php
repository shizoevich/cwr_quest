<?php

namespace App\Jobs\PatientNotes;

use App\Status;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;

class RestoreAppointmentId implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $appointments;
    /**
     * Create a new job instance.
     *
     * @param Collection $appointments
     */
    public function __construct(Collection $appointments)
    {
        $this->appointments = $appointments;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $canceledStatuses = Status::getOtherCancelStatusesId();
        foreach ($this->appointments as $appointment) {
            if (in_array($appointment->appointment_statuses_id, $canceledStatuses) || !$appointment->patient) {
                continue;
            }
            $appointment->patient->patientNotes()
                ->whereNull('appointment_id')
                ->where('date_of_service', '=', Carbon::createFromTimestamp($appointment->time)->toDateString())
                ->where('provider_id', '=', $appointment->providers_id)
                ->each(function ($patientNote) use ($appointment) {
                    $patientNote->update(['appointment_id' => $appointment->id]);
                });
        }
    }
}

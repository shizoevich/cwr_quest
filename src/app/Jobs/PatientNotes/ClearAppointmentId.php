<?php

namespace App\Jobs\PatientNotes;

use App\PatientNote;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;

class ClearAppointmentId implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $appointmentIds;

    /**
     * Create a new job instance.
     *
     * @param \Illuminate\Support\Collection $appointmentIds
     */
    public function __construct(Collection $appointmentIds)
    {
        $this->appointmentIds = $appointmentIds;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        PatientNote::query()
            ->whereIn('appointment_id', $this->appointmentIds)
            ->each(function ($patientNote) {
                $patientNote->update(['appointment_id' => null]);
            });
    }
}

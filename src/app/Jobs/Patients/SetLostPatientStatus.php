<?php

namespace App\Jobs\Patients;

use App\Patient;
use App\PatientStatus;
use App\Status;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SetLostPatientStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $visitCreatedId;

    private $completedId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct() {
        $this->visitCreatedId = Status::getVisitCreatedId();
        $this->completedId = Status::getCompletedId();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $this->newToLost();
        $this->inactiveToLost();
    }

    /**
     * Change patient status from "New" to "Lost"
     */
    private function newToLost() {
        $now = Carbon::now()->subDays(config('patient_statuses.new_to_lost'));
        $patients = Patient::query()
            ->select(['id'])
            ->selectRaw("(
                    SELECT COUNT(id)
                    FROM appointments
                    WHERE deleted_at IS NULL AND appointments.patients_id = patients.id AND appointment_statuses_id IN ({$this->visitCreatedId},{$this->completedId})
                ) AS visit_created_count
            ")
            ->statusNew()
            ->having('visit_created_count', '=', 0)
            ->whereNotNull('created_patient_date')
            ->whereDate('created_patient_date', '<=', $now->toDateString())
            ->get();

        if (!is_null($patients)) {
            PatientStatus::changeStatusAutomatically($patients->pluck('id')->toArray(), 'new_to_lost');
        }
    }

    /**
     * Change patient status from "Inactive" to "Lost"
     */
    private function inactiveToLost() {
        $patients = Patient::query()
            ->select(['id', 'visit_frequency_id'])
            ->selectRaw("(
                    SELECT MAX(time)
                    FROM appointments
                    WHERE deleted_at IS NULL AND appointments.patients_id = patients.id AND appointment_statuses_id IN ({$this->visitCreatedId},{$this->completedId})
                ) AS last_visit_created_time
            ")
            ->statusInactive()
            ->havingRaw('last_visit_created_time IS NOT NULL')
            ->get();

        $now = Carbon::now();

        foreach ($patients as $patient) {
            $lastVisitCreatedTime = Carbon::createFromTimestamp($patient->last_visit_created_time);
            $diffInDays = $lastVisitCreatedTime->diffInDays($now, false);

            $actionName = 'inactive_to_lost';
            $inactiveToLostPeriod = PatientStatus::getChangeStatusPeriod($lastVisitCreatedTime, $actionName, $patient->visit_frequency_id);
    
            if ($diffInDays >= $inactiveToLostPeriod) {
                PatientStatus::changeStatusAutomatically($patient->id, $actionName);
            }
        }
    }
}

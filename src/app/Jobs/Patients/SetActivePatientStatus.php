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

class SetActivePatientStatus implements ShouldQueue
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
        $this->newToActive();
        $this->inactiveToActive();
        $this->lostToActive();
    }

    /**
     * Change patient status from "New" to "Active"
     */
    private function newToActive() {
        $patients = Patient::query()
            ->select(['id'])
            ->selectRaw("(
                    SELECT COUNT(id) 
                    FROM appointments
                    WHERE deleted_at IS NULL AND appointments.patients_id = patients.id AND appointments.appointment_statuses_id IN ({$this->visitCreatedId},{$this->completedId})
                ) AS completed_session_count
            ")
            ->statusNew()
            ->having('completed_session_count', '>', 0)
            ->get();

        if (!empty($patients)) {
            PatientStatus::changeStatusAutomatically($patients->pluck('id')->toArray(), 'new_to_active');
        }
    }

    /**
     * Change patient status from "Inactive" to "Active"
     */
    private function inactiveToActive() {
        $patients = Patient::query()
            ->select(['id', 'visit_frequency_id'])
            ->selectRaw("(
                    SELECT MAX(appointments.time) 
                    FROM appointments
                    WHERE deleted_at IS NULL AND appointments.patients_id = patients.id AND appointments.appointment_statuses_id IN ({$this->visitCreatedId},{$this->completedId})
                ) AS last_visit_created_time
            ")
            ->statusInactive()
            ->havingRaw('last_visit_created_time IS NOT NULL')
            ->get();

        $now = Carbon::now();

        foreach ($patients as $patient) {
            $lastVisitCreatedTime = Carbon::createFromTimestamp($patient->last_visit_created_time);
            $diffInDays = $lastVisitCreatedTime->diffInDays($now, false);

            $actionName = 'inactive_to_active';
            $activeToInactivePeriod = PatientStatus::getChangeStatusPeriod($lastVisitCreatedTime, $actionName, $patient->visit_frequency_id);

            if ($diffInDays < $activeToInactivePeriod) {
                PatientStatus::changeStatusAutomatically($patient->id, $actionName);
            }
        }
    }

    /**
     * Change patient status from "Lost" to "Active"
     */
    private function lostToActive() {
        $now = Carbon::now()->subDays(config('patient_statuses.lost_to_active'));
        $patients = Patient::query()
            ->select(['id'])
            ->selectRaw("(
                    SELECT MAX(time)
                    FROM appointments
                    WHERE deleted_at IS NULL AND appointments.patients_id = patients.id AND appointment_statuses_id IN ({$this->visitCreatedId},{$this->completedId})
                ) AS last_visit_created_time
            ")
            ->statusLost()
            ->having('last_visit_created_time', '>', $now->timestamp)
            ->get();

        if (!empty($patients)) {
            PatientStatus::changeStatusAutomatically($patients->pluck('id')->toArray(), 'lost_to_active');
        }
    }
}

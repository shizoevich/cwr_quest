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
use Illuminate\Support\Facades\DB;

class SetInactivePatientStatus implements ShouldQueue
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
        $this->activeToInactive();
    }

    /**
     * Change patient status from "Active" to "Inactive"
     */
    private function activeToInactive() {
        $patients = Patient::select([
                'id',
                'patient_id',
                'visit_frequency_id',
                DB::raw("(
                    SELECT MAX(time) 
                    FROM appointments 
                    WHERE patients_id = patients.id AND deleted_at IS NULL AND appointment_statuses_id IN ({$this->visitCreatedId},{$this->completedId})
                ) AS last_visit_created_time"),
            ])
            ->statusActive()
            ->havingRaw('last_visit_created_time IS NOT NULL')
            ->get();
        
        $now = Carbon::now();

        foreach ($patients as $patient) {
            $lastVisitCreatedTime = Carbon::createFromTimestamp($patient->last_visit_created_time);
            $diffInDays = $lastVisitCreatedTime->diffInDays($now, false);

            $actionName = 'active_to_inactive';
            $activeToInactivePeriod = PatientStatus::getChangeStatusPeriod($lastVisitCreatedTime, $actionName, $patient->visit_frequency_id);

            if ($diffInDays >= $activeToInactivePeriod) {
                PatientStatus::changeStatusAutomatically($patient->id, $actionName);
            }
        }
    }
}

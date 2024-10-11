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

class SetNewPatientStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $visitCreatedId;
    
    private $completedId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->visitCreatedId = Status::getVisitCreatedId();
        $this->completedId = Status::getCompletedId();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->setNewStatus();
//        $this->lostToNew();
    }

    private function setNewStatus() {
        $patients = Patient::select('id')
            ->whereNull('status_id')
            ->get();

        if(!is_null($patients)) {
            PatientStatus::changeStatusAutomatically($patients->pluck('id')->toArray(), 'to_new');
        }

        $now = Carbon::now()->subDays(config('patient_statuses.new_to_lost'));
        $patients = Patient::select([
            'id',
        ])->selectRaw("(
                    SELECT COUNT(id) 
                    FROM appointments
                    WHERE deleted_at IS NULL AND appointments.patients_id = patients.id AND appointments.appointment_statuses_id IN ({$this->visitCreatedId},{$this->completedId})
                ) AS visit_created_count
            ")
            ->having('visit_created_count', '=', 0)
            ->whereNotNull('created_patient_date')
            ->whereDate('created_patient_date', '>', $now->toDateString())
            ->statusNotNew()
            ->notArchived()
            ->notDischarged()
            ->get();

        if(!is_null($patients)) {
            PatientStatus::changeStatusAutomatically($patients->pluck('id')->toArray(), 'to_new');
        }
    }

    /**
     * Change patient status from "Lost" to "New"
     */
    private function lostToNew() {
        $now = Carbon::now()->subDays(config('patient_statuses.new_to_lost'));
        $patients = Patient::select([
            'id',
        ])->selectRaw("(
                SELECT MAX(time)
                FROM appointments
                WHERE deleted_at IS NULL AND appointments.patients_id = patients.id
            ) AS last_appointment_time, (
                SELECT COUNT(id)
                FROM appointments
                WHERE deleted_at IS NULL AND appointments.patients_id = patients.id AND appointment_statuses_id IN ({$this->visitCreatedId},{$this->completedId})
            ) AS visit_created_count")
            ->statusLost()
            ->having('last_appointment_time', '>', $now->timestamp)
            ->having('visit_created_count', '=', 0)
            ->get();

        if(!is_null($patients)) {
            PatientStatus::changeStatusAutomatically($patients->pluck('id')->toArray(), 'lost_to_new');
        }
    }
}

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

class SetArchivedPatientStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $dischargedId = PatientStatus::getDischargedId();
        $visitCreatedId = Status::getVisitCreatedId();
        $completedId = Status::getCompletedId();
        $patients = Patient::select([
                'patients.id',
                'patients.created_patient_date',
                DB::raw("(
                    SELECT MAX(appointments.time) 
                    FROM appointments
                    WHERE appointments.deleted_at is null AND appointment_statuses_id IN ({$visitCreatedId},{$completedId})
                        AND appointments.patients_id = patients.id
                ) AS appt_time"),
            ])
            ->where('status_id', $dischargedId)
            ->where('patients.id', '!=', 1111)  //Test Patient
            ->whereNotNull('patients.created_patient_date')
            ->groupBy('patients.id')
            ->get();

        $now = Carbon::now();
        foreach($patients as $patient) {
            $dateDiff = 12;
            if(!is_null($patient->appt_time)) {
                $apptTime = Carbon::createFromTimestamp($patient->appt_time);
                $dateDiff = $apptTime->diffInMonths($now, false);
            } else if(!is_null($patient->created_patient_date)) {
                $createdDate = Carbon::parse($patient->created_patient_date);
                $dateDiff = $createdDate->diffInMonths($now, false);
            } else if(is_null($patient->created_patient_date)) {
                $dateDiff = 0;
            }
            if($dateDiff >= 12) {
                PatientStatus::changeStatusAutomatically($patient->id, 'discharged_to_archived');
            }
        }
    }
}

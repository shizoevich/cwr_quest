<?php

namespace App\Jobs\Salary;

use App\Appointment;
use App\Models\Provider\Salary;
use App\Models\Provider\SalaryTimesheetVisit;
use App\PatientVisit;
use App\Status;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Illuminate\Database\Query\JoinClause;

class AssignVisitsToAppointments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Carbon|null
     */
    private $startDate;
    /**
     * @var Carbon|null
     */
    private $endDate;

    /**
     * Create a new job instance.
     *
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     */
    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $notAssignedCount = 0;
        $assignedCount = 0;
        PatientVisit::query()
            ->whereNotNull('patient_id')
            ->whereNotNull('provider_id')
            ->whereNull('appointment_id')
            ->where('from_completed_appointment', '=', 0)
            ->when($this->startDate, function ($query, $startDate) {
                $query->where('date', '>=', $startDate->toDateString());
            })
            ->when($this->endDate, function ($query, $endDate) {
                $query->where('date', '<=', $endDate->toDateString());
            })
            ->chunkById(100, function (Collection $visits) use (&$assignedCount, &$notAssignedCount) {
                $dates = $visits->pluck('date')->unique()->toArray();
                $appointments = Appointment::query()
                    ->select([
                        'appointments.id',
                        'appointments.patients_id',
                        'appointments.providers_id',
                        \DB::raw('0 AS assigned'),
                        \DB::raw('DATE(FROM_UNIXTIME(`time`)) AS appt_date')
                    ])
                    ->leftJoin('patient_visits', function(JoinClause $join) {
                        $join->on('patient_visits.appointment_id', '=', 'appointments.id')
                            ->where('patient_visits.from_completed_appointment', '=', 0);
                    })
                    ->whereIn('appointment_statuses_id', Status::getCompletedVisitCreatedStatusesId())
                    ->whereIn(\DB::raw('DATE(FROM_UNIXTIME(`time`))'), $dates)
                    ->whereNull('patient_visits.id')
                    ->get()
                    ->groupBy('appt_date');

                foreach ($visits as $visit) {
                    if (!isset($appointments[$visit->date])) {
                        $notAssignedCount++;
                        continue;
                    }

                    $appointment = $appointments[$visit->date]
                        ->where('patients_id', '=', $visit->patient_id)
                        ->where('providers_id', '=', $visit->provider_id)
                        ->where('assigned', '=', 0)
                        ->first();

                    if (!$appointment) {
                        $notAssignedCount++;
                        continue;
                    }

                    $patientVisit = PatientVisit::find($visit->id); // load visit again to prevent problems with updating
                    $patientVisit->update([
                        'appointment_id' => $appointment->id,
                        'needs_update_salary' => 1
                    ]);
                    
                    $appointment->assigned = 1;
                    $assignedCount++;

                    if (config('app.visits_with_completed_appointments_to_salary') == true) {
                        PatientVisit::query()
                            ->where('appointment_id', '=', $appointment->id)
                            ->where('from_completed_appointment', '=', 1)
                            ->each(function ($patientVisitFromCompleted) {
                                Salary::where('visit_id', $patientVisitFromCompleted->id)->delete();
                                SalaryTimesheetVisit::where('visit_id', $patientVisitFromCompleted->id)->delete();
                                $patientVisitFromCompleted->delete();
                            });
                    }
                }
            });

        //        echo "Assigned: $assignedCount" . PHP_EOL;
        //        echo "Not Assigned: $notAssignedCount" . PHP_EOL;
    }
}

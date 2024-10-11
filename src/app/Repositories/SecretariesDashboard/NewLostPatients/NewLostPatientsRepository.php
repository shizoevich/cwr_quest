<?php

namespace App\Repositories\SecretariesDashboard\NewLostPatients;

use App\Appointment;
use App\Patient;
use App\PatientStatus;
use App\Status;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class NewLostPatientsRepository implements NewLostPatientsRepositoryInterface
{
    public function getNewPatients(Carbon $startDate, Carbon $endDate): array
    {
        $patientStatusNewId = PatientStatus::getNewId();

        $firstAppointmentQuery = 'SELECT appointments.id FROM appointments WHERE appointments.patients_id=patients.id LIMIT 1';

        $patients = Patient::query()
            ->select([
                'id',
                'status_id',
                'first_name',
                'last_name',
                'cell_phone',
                'home_phone',
                'work_phone',
                'created_at',
                'created_patient_date',
                DB::raw("DATE_FORMAT(status_updated_at, '%m/%d/%Y') AS status_updated_at"),
                DB::raw("($firstAppointmentQuery) AS first_appointment_id"),
            ])
            ->where('status_id', $patientStatusNewId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_patient_date', [$startDate, $endDate])
                    ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                        $subQuery->whereNull('created_patient_date')
                            ->whereBetween('created_at', [$startDate, $endDate]);
                    });
            })
            ->havingRaw('first_appointment_id IS NULL')
            ->with([
                'status:id,status,hex_color',
                'lastRingcentralCallLog',
                'lastRingcentralCallLog.user' => function ($query) {
                    $query->withTrashed()->select('id');
                },
                'lastRingcentralCallLog.user.meta' => function ($query) {
                    $query->withTrashed()->select(['user_id', 'firstname', 'lastname']);
                },
            ])
            ->withCount('ringcentralCallLogs')
            ->get();

        return [
            'data' => $patients,
            'meta' => [
                'count' => $patients->count(),
            ]
        ];
    }

    public function getInactivePatients(Carbon $startDate, Carbon $endDate): array
    {
        $patients = $this->getPatientsUpdatedForPeriod(
            $startDate,
            $endDate,
            PatientStatus::getInactiveId()
        );

        return [
            'data' => $patients,
            'meta' => [
                'count' => $patients->count(),
            ]
        ];
    }

    public function getLostPatients(Carbon $startDate, Carbon $endDate): array
    {
        $patients = $this->getPatientsUpdatedForPeriod(
            $startDate,
            $endDate,
            PatientStatus::getLostId()
        );

        return [
            'data' => $patients,
            'meta' => [
                'count' => $patients->count(),
            ]
        ];
    }

    private function getPatientsUpdatedForPeriod(Carbon $startDate, Carbon $endDate, int $statusId): Collection
    {
        $patientsUpdatedForPeriod = DB::connection('mysql_logger')
            ->table('hipaa_log_item')
            ->select(['dirty_fields', 'data'])
            ->whereBetween('appeared_at', [$startDate, $endDate])
            ->where('event_name_id', Patient::getEventNamePatientUpdate())
            ->where('dirty_fields', 'like', '%status_id%')
            ->get()
            ->filter(function ($hipaaLogItem) use ($statusId) {
                return json_decode($hipaaLogItem->dirty_fields)->status_id->curr === $statusId;
            })
            ->map(function ($hipaaLogItem) {
                return json_decode($hipaaLogItem->data)->id;
            })
            ->unique()
            ->values()
            ->all();

        $completedStatusId = Status::getCompletedId();
        $visitCreatedStatusId = Status::getVisitCreatedId();

        $lastAppointmentQuery = "SELECT appointments.id FROM appointments 
            WHERE appointments.patients_id = patients.id 
            AND appointments.appointment_statuses_id IN ($completedStatusId, $visitCreatedStatusId) 
            AND appointments.deleted_at IS NULL 
            ORDER BY time DESC LIMIT 1";

        $patients = Patient::query()
            ->select([
                'id',
                'status_id',
                'first_name',
                'last_name',
                DB::raw("DATE_FORMAT(status_updated_at, '%m/%d/%Y') AS status_updated_at"),
                DB::raw("($lastAppointmentQuery) AS last_appointment_id")
            ])
            ->where('status_id', $statusId)
            ->whereIn('id', $patientsUpdatedForPeriod)
            ->with('status:id,status,hex_color')
            ->withCount([
                'appointments' => function ($query) use ($completedStatusId, $visitCreatedStatusId) {
                    $query->whereIn('appointment_statuses_id', [
                        $completedStatusId,
                        $visitCreatedStatusId
                    ]);
                }
            ])->get();

        $this->loadPatientsLastAppointment($patients);

        return $patients;
    }

    private function loadPatientsLastAppointment(Collection $patients): void
    {
        $completedStatusId = Status::getCompletedId();
        $visitCreatedStatusId = Status::getVisitCreatedId();

        $lastAppointments = Appointment::query()
            ->select([
                'id',
                'time',
                'providers_id',
                'appointment_statuses_id',
            ])
            ->with([
                'provider' => function ($query) {
                    return $query->withTrashed()->select('id', 'provider_name');
                },
                'status:id,status',
            ])
            ->whereIn('id', $patients->where('last_appointment_id', '!=', null)->pluck('last_appointment_id'))
            ->whereIn('appointment_statuses_id', [$completedStatusId, $visitCreatedStatusId])
            ->get();

        $patients->each(function ($patient) use ($lastAppointments) {
            $patient->last_appointment = $lastAppointments->where('id', $patient->last_appointment_id)->first();
        });
    }
}

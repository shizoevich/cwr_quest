<?php

namespace App\Helpers\StatisticsReport;

use App\Appointment;
use App\Status;
use Carbon\Carbon;

class VisitsWithoutFutureAppointmentsStatisticsReportHelper implements AbstractStatisticsReportHelper
{
    public static function getData(string $startDate, string $endDate): array
    {
        $data = [];
        $completedVisitCreatedIds = Status::getCompletedVisitCreatedStatusesId();
        $cancelIds = Status::getNewCancelStatusesId();

        $patientsLastAppointmentsSql = 'SELECT patients_id as patient_id, MAX(time) as max_time FROM appointments WHERE deleted_at IS NULL AND appointment_statuses_id NOT IN (' . implode(",", $cancelIds). ') AND time >= ' . Carbon::parse($startDate)->timestamp . ' GROUP BY patients_id';

        Appointment::query()
            ->select([
                'appointments.id',
                'patients.id AS patient_id',
                \DB::raw("CONCAT(`patients`.`first_name`, ' ', `patients`.`last_name`) AS patient_name"),
                'providers.provider_name',
                'users.email AS provider_email',
                \DB::raw('FROM_UNIXTIME(`appointments`.`time`) AS appt_date'),
                'patient_statuses.status AS patient_status',
                'patient_visit_frequencies.name AS patient_visit_frequency'
            ])
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->join('patient_statuses', 'patient_statuses.id', '=', 'patients.status_id')
            ->join('patient_visit_frequencies', 'patient_visit_frequencies.id', '=', 'patients.visit_frequency_id')
            ->join('providers', 'providers.id', '=', 'appointments.providers_id')
            ->join('users', 'users.provider_id', '=', 'providers.id')
            ->join(
                \DB::raw("($patientsLastAppointmentsSql) as lpa"),
                function ($join) {
                    $join->on('appointments.patients_id', '=', 'lpa.patient_id');
                    $join->on('appointments.time', '=', 'lpa.max_time');
                }
            )
            ->where('patients.is_test', '=', 0)
            ->where('providers.is_test', '=', 0)
            ->whereIn('appointment_statuses_id', $completedVisitCreatedIds)
            ->havingRaw("DATE(appt_date) >= DATE('{$startDate}')")
            ->havingRaw("DATE(appt_date) <= DATE('{$endDate}')")
            ->orderBy('appointments.time')
            ->each(function ($appointment) use (&$data) {
                $data[] = [
                    'Patient ID' => $appointment->patient_id,
                    'Patient Name' => $appointment->patient_name,
                    'Patient Status' => $appointment->patient_status,
                    'Frequency of Treatment' =>  $appointment->patient_visit_frequency,
                    'Provider Name' => $appointment->provider_name,
                    'Provider Email' => $appointment->provider_email,
                    'Date of Service' => $appointment->appt_date,
                    'EHR Link' => 'https://admin.cwr.care/chart/' . $appointment->patient_id,
                ];
            });

        return $data;
    }

    public static function getColumnNames(): array
    {
        return [
            'Patient ID',
            'Patient Name',
            'Patient Status',
            'Frequency of Treatment',
            'Provider Name',
            'Provider Email',
            'Date of Service',
            'EHR Link',
        ];
    }
}
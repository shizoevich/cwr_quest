<?php

namespace App\Helpers\StatisticsReport;

use App\Appointment;
use App\Status;

class ActiveAppointmentsStatisticsReportHelper implements AbstractStatisticsReportHelper
{
    public static function getData(string $startDate, string $endDate): array
    {
        $data = [];
        $activeId = Status::getActiveId();

        Appointment::query()
            ->select([
                'patients.id AS patient_id',
                \DB::raw("CONCAT(`patients`.`first_name`, ' ', `patients`.`last_name`) AS patient_name"),
                'providers.provider_name',
                'users.email AS provider_email',
                \DB::raw('DATE(FROM_UNIXTIME(`appointments`.`time`)) AS appt_date')
            ])
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->join('providers', 'providers.id', '=', 'appointments.providers_id')
            ->join('users', 'users.provider_id', '=', 'providers.id')
            ->where('patients.is_test', '=', 0)
            ->where('providers.is_test', '=', 0)
            ->where('appointment_statuses_id', $activeId)
            ->havingRaw("appt_date >= DATE('{$startDate}')")
            ->havingRaw("appt_date <= DATE('{$endDate}')")
            ->orderBy('appointments.time')
            ->each(function ($appointment) use (&$data) {
                $data[] = [
                    'Patient ID' => $appointment->patient_id,
                    'Patient Name' => $appointment->patient_name,
                    'Provider Name' => $appointment->provider_name,
                    'Provider Email' => $appointment->provider_email,
                    'Date of Service' => $appointment->appt_date,
                    'EHR Link' => "https://admin.cwr.care/chart/{$appointment->patient_id}",
                ];
            });

        return $data;
    }

    public static function getColumnNames(): array
    {
        return [
            'Patient ID',
            'Patient Name',
            'Provider Name',
            'Provider Email',
            'Date of Service',
            'EHR Link',
        ];
    }
}
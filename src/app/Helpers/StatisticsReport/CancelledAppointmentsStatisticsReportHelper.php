<?php

namespace App\Helpers\StatisticsReport;

use App\Appointment;
use App\Status;

class CancelledAppointmentsStatisticsReportHelper implements AbstractStatisticsReportHelper
{
    public static function getData(string $startDate, string $endDate): array
    {
        $data = [];
        $cancelIds = Status::getOtherCancelStatusesId();
        $rescheduledId = Status::getRescheduledId();

        Appointment::query()
            ->select([
                'appointments.id',
                'patients.id AS patient_id',
                \DB::raw("CONCAT(`patients`.`first_name`, ' ', `patients`.`last_name`) AS patient_name"),
                'providers.provider_name',
                'users.email AS provider_email',
                \DB::raw('DATE(FROM_UNIXTIME(`appointments`.`time`)) AS appt_date'),
                'appointments.appointment_statuses_id',
                'appointment_statuses.status',
                'appointments.custom_notes'
            ])
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->join('providers', 'providers.id', '=', 'appointments.providers_id')
            ->join('users', 'users.provider_id', '=', 'providers.id')
            ->leftJoin('appointment_statuses', 'appointment_statuses.id', '=', 'appointments.appointment_statuses_id')
            ->where('patients.is_test', '=', 0)
            ->where('providers.is_test', '=', 0)
            ->whereIn('appointments.appointment_statuses_id', $cancelIds)
            ->havingRaw("appt_date >= DATE('{$startDate}')")
            ->havingRaw("appt_date <= DATE('{$endDate}')")
            ->orderBy('appointments.time')
            ->each(function ($appointment) use (&$data, $rescheduledId) {
                $rescheduledTo = '';
                if ($appointment->appointment_statuses_id === $rescheduledId) {
                    $rescheduledTo = self::getRescheduledToDate($appointment->id);
                }

                $data[] = [
                    'Patient ID' => $appointment->patient_id,
                    'Patient Name' => $appointment->patient_name,
                    'Provider Name' => $appointment->provider_name,
                    'Provider Email' => $appointment->provider_email,
                    'Date of Service' => $appointment->appt_date,
                    'Appointment Status' => $appointment->status,
                    'Rescheduled to' => $rescheduledTo,
                    'Comment' => $appointment->custom_notes ?? '',
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
            'Appointment Status',
            'Rescheduled to',
            'Comment',
            'EHR Link',
        ];
    }

    private static function getRescheduledToDate($appointmentId)
    {
        return Appointment::query()
            ->select([
                \DB::raw('DATE(FROM_UNIXTIME(`appointments`.`time`)) AS appt_date'),
            ])
            ->where('rescheduled_appointment_id', $appointmentId)
            ->first()['appt_date'];
    }
}
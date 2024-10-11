<?php

namespace App\Helpers\StatisticsReport;

use App\Appointment;
use App\Models\RingcentralCallLog;
use App\Status;
use Carbon\Carbon;

class OnlineVisitsWithoutLogsStatisticsReportHelper implements AbstractStatisticsReportHelper
{
    public static function getData(string $startDate, string $endDate): array
    {
        $data = [];
        $completedVisitCreatedIds = Status::getCompletedVisitCreatedStatusesId();

        Appointment::query()
            ->select([
                'patients.id AS patient_id',
                \DB::raw("CONCAT(`patients`.`first_name`, ' ', `patients`.`last_name`) AS patient_name"),
                'providers.provider_name',
                'users.email AS provider_email',
                \DB::raw('FROM_UNIXTIME(`appointments`.`time`) AS appt_date'),
                'appointments.visit_length'
            ])
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->join('providers', 'providers.id', '=', 'appointments.providers_id')
            ->join('users', 'users.provider_id', '=', 'providers.id')
            ->where('patients.is_test', '=', 0)
            ->where('providers.is_test', '=', 0)
            ->whereIn('appointment_statuses_id', $completedVisitCreatedIds)
            ->where('appointments.reason_for_visit', '=', 'Telehealth')
            ->whereDoesntHave('ringcentralCallLogs')
            ->whereDoesntHave('googleMeet', function ($query) {
                $query->whereHas('callLogs');
            })
            ->havingRaw("DATE(appt_date) >= DATE('{$startDate}')")
            ->havingRaw("DATE(appt_date) <= DATE('{$endDate}')")
            ->orderBy('appointments.time')
            ->each(function ($appointment) use (&$data) {
                $data[] = [
                    'Patient ID' => $appointment->patient_id,
                    'Patient Name' => $appointment->patient_name,
                    'Provider Name' => $appointment->provider_name,
                    'Provider Email' => $appointment->provider_email,
                    'Date of Service' => $appointment->appt_date,
                    'Patient Call Log Exists' => self::checkPatientCallLogs($appointment->patient_id, $appointment->appt_date, $appointment->visit_length) ? 'Yes' : 'No',
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
            'Patient Call Log Exists',
            'EHR Link',
        ];
    }

    private static function checkPatientCallLogs($patientId, $apptDate, $apptLength): bool
    {
        $apptStart = Carbon::parse($apptDate);
        $apptEnd = $apptStart->copy()->addMinutes($apptLength);

        return RingcentralCallLog::query()
            ->where('call_subject_id', $patientId)
            ->where('call_subject_type', 'App\Patient')
            ->where(function ($query) use (&$apptStart, &$apptEnd) {
                $query
                    ->where(function ($query) use (&$apptStart, &$apptEnd) {
                        $query->where('call_starts_at', '>=', $apptStart->toDateTimeString())
                            ->where('call_starts_at', '<', $apptEnd->toDateTimeString());
                    })
                    ->orWhere(function ($query) use (&$apptStart, &$apptEnd) {
                        $query->where('call_ends_at', '>', $apptStart->toDateTimeString())
                            ->where('call_ends_at', '<=', $apptEnd->toDateTimeString());
                    });
            })
            ->exists();
    }
}
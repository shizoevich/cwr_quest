<?php

namespace App\Helpers\StatisticsReport;

use App\Appointment;
use App\Status;
use Carbon\Carbon;

class OnlineVisitsWithCallLogsStatisticsReportHelper implements AbstractStatisticsReportHelper
{
    public static function getData(string $startDate, string $endDate): array
    {
        $data = [];
        $completedVisitCreatedIds = Status::getCompletedVisitCreatedStatusesId();

        Appointment::query()
            ->select([
                'appointments.id',
                'patients.id AS patient_id',
                \DB::raw("CONCAT(`patients`.`first_name`, ' ', `patients`.`last_name`) AS patient_name"),
                'providers.provider_name',
                'users.email AS provider_email',
                \DB::raw('FROM_UNIXTIME(`appointments`.`time`) AS appt_date')
            ])
            ->with('ringcentralCallLogs')
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->join('providers', 'providers.id', '=', 'appointments.providers_id')
            ->join('users', 'users.provider_id', '=', 'providers.id')
            ->where('patients.is_test', '=', 0)
            ->where('providers.is_test', '=', 0)
            ->whereIn('appointment_statuses_id', $completedVisitCreatedIds)
            ->where('appointments.reason_for_visit', '=', 'Telehealth')
            ->whereHas('ringcentralCallLogs')
            ->havingRaw("DATE(appt_date) >= DATE('{$startDate}')")
            ->havingRaw("DATE(appt_date) <= DATE('{$endDate}')")
            ->orderBy('appointments.time')
            ->each(function ($appointment) use (&$data) {
                $duration = self::getCallLogsDuration($appointment->ringcentralCallLogs);

                $data[] = [
                    'Patient ID' => $appointment->patient_id,
                    'Patient Name' => $appointment->patient_name,
                    'Provider Name' => $appointment->provider_name,
                    'Provider Email' => $appointment->provider_email,
                    'Date of Service' => $appointment->appt_date,
                    'Call Logs Duration' => "{$duration}",
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
            'Call Logs Duration',
            'EHR Link',
        ];
    }

    private static function getCallLogsDuration($logs)
    {
        $duration = 0;

        $logs->each(function ($log) use (&$duration) {
            if (empty($log->call_starts_at) || empty($log->call_ends_at)) {
                return;
            }

            $callStartTime = Carbon::createFromFormat("Y-m-d H:i:s", $log->call_starts_at);
            $callEndTime = Carbon::createFromFormat("Y-m-d H:i:s", $log->call_ends_at);

            $duration += $callEndTime->diffInMinutes($callStartTime);
        });

        return $duration;
    }
}
<?php

namespace App\Helpers\StatisticsReport;

use App\Appointment;
use App\Status;

class VisitsWithNegativeBalanceStatisticsReportHelper implements AbstractStatisticsReportHelper
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
                \DB::raw('(SELECT `ptp`.`balance_after_transaction` / 100 FROM `patient_preprocessed_transactions` `ptp` WHERE `ptp`.`patient_id`=`appointments`.`patients_id` AND `ptp`.`created_at` <= FROM_UNIXTIME(`appointments`.`time`) ORDER BY `ptp`.`created_at` DESC LIMIT 1) AS balance'),
                \DB::raw('(SELECT `ptp`.`balance_after_transaction` / 100 FROM `patient_preprocessed_transactions` `ptp` WHERE `ptp`.`patient_id`=`appointments`.`patients_id` ORDER BY `ptp`.`created_at` DESC LIMIT 1) AS current_balance'),
                \DB::raw('FROM_UNIXTIME(`appointments`.`time`) AS appt_date'),
            ])
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->join('providers', 'providers.id', '=', 'appointments.providers_id')
            ->join('users', 'users.provider_id', '=', 'providers.id')
            ->where('patients.is_test', '=', 0)
            ->where('providers.is_test', '=', 0)
            ->whereIn('appointment_statuses_id', $completedVisitCreatedIds)
            ->havingRaw("DATE(appt_date) >= DATE('{$startDate}')")
            ->havingRaw("DATE(appt_date) <= DATE('{$endDate}')")
            ->havingRaw('balance < 0')
            ->orderBy('appointments.time')
            ->each(function ($appointment) use (&$data) {
                $data[] = [
                    'Patient ID' => $appointment->patient_id,
                    'Patient Name' => $appointment->patient_name,
                    'Provider Name' => $appointment->provider_name,
                    'Provider Email' => $appointment->provider_email,
                    'Date of Service' => $appointment->appt_date,
                    'Balance Before Appt.' => $appointment->balance ?: '0', // int value of zero is not displaying in xlsx
                    'Current Balance' => $appointment->current_balance ?: '0',
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
            'Provider Name',
            'Provider Email',
            'Date of Service',
            'Balance Before Appointment',
            'Current Balance',
            'EHR Link',
        ];
    }
}
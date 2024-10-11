<?php

namespace App\Helpers\StatisticsReport;

use App\Appointment;
use App\Models\PatientHasProvider;
use App\Patient;
use App\PatientStatus;
use App\Status;

class InactiveOrLostPatientsStatisticsReportHelper implements AbstractStatisticsReportHelper
{
    public static function getData(string $startDate, string $endDate): array
    {
        $data = [];
        $inactiveId = PatientStatus::getInactiveId();
        $lostId = PatientStatus::getLostId();

        Patient::query()
            ->select([
                'patients.id',
                \DB::raw("CONCAT(`patients`.`first_name`, ' ', `patients`.`last_name`) AS patient_name"),
                'patient_statuses.status',
                'patients.status_updated_at'
            ])
            ->leftJoin('patient_statuses', 'patient_statuses.id', '=', 'patients.status_id')
            ->where('patients.is_test', '=', 0)
            ->whereIn('patients.status_id', [$inactiveId, $lostId])
            ->whereDate('patients.status_updated_at', '>=', $startDate)
            ->whereDate('patients.status_updated_at', '<=', $endDate)
            ->orderBy('patients.status_updated_at')
            ->each(function ($patient) use (&$data, $endDate) {
                $lastProviderDetails = self::getLastProviderDetails($patient->id, $endDate);

                $data[] = [
                    'Patient ID' => $patient->id,
                    'Patient Name' => $patient->patient_name,
                    'Patient Status' => $patient->status,
                    'Status Changed At' => $patient->status_updated_at,
                    'Provider Name' => $lastProviderDetails['Provider Name'],
                    'Provider Email' => $lastProviderDetails['Provider Email'],
                    'Last Visit Date' => $lastProviderDetails['Last Visit Date'],
                    'EHR Link' => "https://admin.cwr.care/chart/{$patient->id}",
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
            'Status Changed At',
            'Provider Name',
            'Provider Email',
            'Last Visit Date',
            'EHR Link',
        ];
    }

    private static function getLastProviderDetails(int $patientId, string $dateTo)
    {
        $lastVisit = Appointment::query()
            ->select([
                'providers.provider_name',
                'users.email AS provider_email',
                \DB::raw('DATE(FROM_UNIXTIME(`appointments`.`time`)) AS appt_date')
            ])
            ->join('providers', 'providers.id', '=', 'appointments.providers_id')
            ->join('users', 'users.provider_id', '=', 'providers.id')
            ->where('providers.is_test', '=', 0)
            ->where('appointments.patients_id', '=', $patientId)
            ->whereIn('appointment_statuses_id', Status::getCompletedVisitCreatedStatusesId())
            ->havingRaw("appt_date <= DATE('{$dateTo}')")
            ->orderByDesc('appointments.time')
            ->first();

        if (isset($lastVisit)) {
            return [
                'Provider Name' => $lastVisit->provider_name,
                'Provider Email' => $lastVisit->provider_email,
                'Last Visit Date' => $lastVisit->appt_date,
            ];
        }

        $lastProvider = PatientHasProvider::query()
            ->select([
                'providers.provider_name',
                'users.email AS provider_email'
            ])
            ->join('providers', 'providers.id', '=', 'patients_has_providers.providers_id')
            ->join('users', 'users.provider_id', '=', 'providers.id')
            ->where('providers.is_test', '=', 0)
            ->where('patients_has_providers.patients_id', $patientId)
            ->where('chart_read_only', 0)
            ->first();

        if (isset($lastProvider)) {
            return [
                'Provider Name' => $lastProvider->provider_name,
                'Provider Email' => $lastProvider->provider_email,
                'Last Visit Date' => '',
            ];
        }

        return [
            'Provider Name' => '',
            'Provider Email' => '',
            'Last Visit Date' => '',
        ];
    }
}
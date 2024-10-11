<?php

namespace App\Helpers\StatisticsReport;

use App\Appointment;
use App\Models\PatientHasProvider;
use App\Patient;
use App\PatientStatus;
use App\PatientVisitFrequency;
use App\Status;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class PatientsWithMissedAppointmentsStatisticsReportHelper implements AbstractStatisticsReportHelper
{
    public static function getData(string $startDate, string $endDate): array
    {
        $data = [];
        $activeId = PatientStatus::getActiveId();
        $endDateWeekStart = Carbon::parse($endDate)->startOfWeek();

        Patient::query()
            ->select([
                'patients.id',
                \DB::raw("CONCAT(`patients`.`first_name`, ' ', `patients`.`last_name`) AS patient_name"),
                'patient_statuses.status',
                'patients.status_updated_at',
                'patients.status_id',
                'patient_insurances.insurance',
                \DB::raw("(
                    SELECT `patients_visit_frequency_changes`.`created_at`
                    FROM `patients_visit_frequency_changes`
                    WHERE `patients_visit_frequency_changes`.`patient_id` = `patients`.`id`
                    ORDER BY `patients_visit_frequency_changes`.`created_at` DESC
                    LIMIT 1
                ) AS visit_frequency_updated_at"),
                \DB::raw("DATE(COALESCE(`patients`.`created_patient_date`, `patients`.`created_at`)) as patient_created_at"),
            ])
            ->leftJoin('patient_statuses', 'patient_statuses.id', '=', 'patients.status_id')
            ->leftJoin('patient_insurances', 'patient_insurances.id', 'patients.primary_insurance_id')
            ->where('patients.is_test', '=', 0)
            ->where('patients.status_id', '=', $activeId)
            ->where('patients.visit_frequency_id', PatientVisitFrequency::getWeeklyId())
            ->havingRaw("(
                (visit_frequency_updated_at IS NULL AND DATE(patient_created_at) < DATE('{$endDateWeekStart->toDateString()}'))
                    OR DATE(visit_frequency_updated_at) < DATE('{$endDateWeekStart->toDateString()}')
            )")
            ->each(function ($patient) use (&$data, $startDate, $endDate) {
                $start = Carbon::parse($patient->patient_created_at)->startOfDay();
                if (isset($patient->visit_frequency_updated_at)) {
                    $start = Carbon::parse($patient->visit_frequency_updated_at)->startOfDay();
                }
                if ($start->copy()->startOfWeek()->lt($start)) {
                    // if start date is not monday, then start from next week
                    $start = $start->copy()->startOfWeek()->addWeek();
                }
                if ($start->lt($startDate)) {
                    // if start date less then search period start date, then start from period start date
                    $start = Carbon::parse($startDate);
                }

                $end = Carbon::parse($endDate)->endOfWeek();
                
                foreach (CarbonPeriod::create($start, '1 week', $end) as $weekStart) {
                    $weekEnd = $weekStart->copy()->endOfWeek();
                    $appointment = Appointment::query()
                        ->where('patients_id', $patient->id)
                        ->whereRaw("DATE(FROM_UNIXTIME(`appointments`.`time`)) >= '{$weekStart->toDateString()}'")
                        ->whereRaw("DATE(FROM_UNIXTIME(`appointments`.`time`)) <= '{$weekEnd->toDateString()}'")
                        ->first();

                    if (isset($appointment)) {
                        continue;
                    }

                    $lastProviderDetails = self::getLastProviderDetails($patient->id, $weekStart->copy()->subDay());
                    $nextAppt = Appointment::query()
                        ->select([
                            \DB::raw('DATE(FROM_UNIXTIME(`appointments`.`time`)) AS appt_date'),
                            'appointment_statuses.status'
                        ])
                        ->join('appointment_statuses', 'appointment_statuses.id', '=', 'appointments.appointment_statuses_id')
                        ->where('patients_id', $patient->id)
                        ->whereIn('appointment_statuses_id', Status::getActiveCompletedVisitCreatedStatusesId())
                        ->whereRaw("DATE(FROM_UNIXTIME(`appointments`.`time`)) >= '{$weekEnd->toDateString()}'")
                        ->first();
                    
                    $data[] = [
                        'Patient ID' => $patient->id,
                        'Patient Name' => $patient->patient_name,
                        'Patient Status' => $patient->status,
                        'Insurance' => $patient->insurance,
                        'Status Changed At' => $patient->status_updated_at,
                        'Visit Frequency Changed At' => $patient->visit_frequency_updated_at,
                        'Appt. Missed For Period' => $weekStart->toDateString() . ' - ' . $weekEnd->toDateString(),
                        'Provider Name' => $lastProviderDetails['Provider Name'],
                        'Provider Email' => $lastProviderDetails['Provider Email'],
                        'Previous Visit Date' => $lastProviderDetails['Last Visit Date'],
                        'Next Appt. Date (Active/Completed/Visit Created)' => isset($nextAppt) ? "$nextAppt->appt_date ({$nextAppt->status})" : '',
                        'EHR Link' => "https://admin.cwr.care/chart/{$patient->id}",
                    ];
                }
            });

        return $data;
    }

    public static function getColumnNames(): array
    {
        return [
            'Patient ID',
            'Patient Name',
            'Patient Status',
            'Insurance',
            'Status Changed At',
            'Visit Frequency Changed At',
            'Appt. Missed For Period',
            'Provider Name',
            'Provider Email',
            'Previous Visit Date',
            'Next Appt. Date (Active/Completed/Visit Created)',
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
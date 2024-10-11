<?php

namespace App\Helpers\StatisticsReport;

use App\Appointment;
use App\Models\Patient\DocumentRequest\PatientDocumentRequest;
use App\Models\Patient\DocumentRequest\PatientFormType;
use App\Patient;
use App\PatientStatus;
use App\Status;

class VisitsWithoutCompletedFormsStatisticsReportHelper implements AbstractStatisticsReportHelper
{
    private const REQUIRED_FORMS = [
        'new_patient',
        'payment_for_service',
        'agreement_for_service_and_hipaa_privacy_notice_and_patient_rights'
    ];

    public static function getData(string $startDate, string $endDate): array
    {
        $data = [];
        $completedVisitCreatedIds = Status::getCompletedVisitCreatedStatusesId();
        $patientStatusArchivedId = PatientStatus::getArchivedId();

        $patientsWithoutForms = Patient::query()
            ->select('patients.id')
            ->where('patients.status_id', '!=', $patientStatusArchivedId)
            ->whereNotCompletedIntakeForms()
            ->get()
            ->pluck('id');

        Appointment::query()
            ->select([
                'appointments.id',
                'patients.id AS patient_id',
                \DB::raw("CONCAT(`patients`.`first_name`, ' ', `patients`.`last_name`) AS patient_name"),
                'providers.provider_name',
                'users.email AS provider_email',
                \DB::raw('FROM_UNIXTIME(`appointments`.`time`) AS appt_date'),
            ])
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->join('providers', 'providers.id', '=', 'appointments.providers_id')
            ->join('users', 'users.provider_id', '=', 'providers.id')
            ->where('patients.is_test', '=', 0)
            ->where('providers.is_test', '=', 0)
            ->whereIn('appointments.patients_id', $patientsWithoutForms)
            ->whereIn('appointment_statuses_id', $completedVisitCreatedIds)
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
                    'Forms Sent At' => self::getFormsSentAt($appointment->patient_id) ?? '-',
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
            'Forms Sent At',
            'EHR Link',
        ];
    }

    private static function getFormsSentAt($patientId)
    {
        $requiredFormTypeIds = PatientFormType::getFormTypeIds(self::REQUIRED_FORMS);

        $documentRequest = PatientDocumentRequest::query()
            ->select([
                \DB::raw('DATE_FORMAT(created_at, "%m/%d/%Y %h:%i %p") as date'),
            ])
            ->where('patient_id', $patientId)
            ->whereHas('items', function ($query) use (&$requiredFormTypeIds) {
                $query->whereIn('form_type_id', $requiredFormTypeIds);
            })
            ->orderBy('created_at', 'DESC')
            ->first();

        return optional($documentRequest)->date;
    }
}
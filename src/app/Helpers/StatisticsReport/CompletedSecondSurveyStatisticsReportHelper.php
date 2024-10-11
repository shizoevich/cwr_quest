<?php

namespace App\Helpers\StatisticsReport;

use App\PatientComment;

class CompletedSecondSurveyStatisticsReportHelper implements AbstractStatisticsReportHelper
{
    public static function getData(string $startDate, string $endDate): array
    {
        $data = [];

        PatientComment::query()
            ->select([
                'patient_comments.id',
                'patient_comments.comment',
                'patient_comments.metadata',
                'patient_comments.created_at',
                'patients.id AS patient_id',
                \DB::raw("CONCAT(`patients`.`first_name`, ' ', `patients`.`last_name`) AS patient_name"),
                'patient_statuses.status',
                'providers.provider_name',
                'users.email AS provider_email',
                \DB::raw('FROM_UNIXTIME(`appointments`.`time`) AS appt_date'),
                \DB::raw('(SELECT COUNT(`appointments`.`id`) FROM appointments WHERE `appointments`.`patients_id` = `patients`.`id` AND `appointments`.`appointment_statuses_id` IN (1, 7) AND `appointments`.`deleted_at` IS NULL) AS visits_count'),
            ])
            ->join('appointments', 'appointments.id', 'patient_comments.appointment_id')
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->join('providers', 'providers.id', '=', 'appointments.providers_id')
            ->join('users', 'users.provider_id', '=', 'providers.id')
            ->join('patient_statuses', 'patient_statuses.id', '=', 'patients.status_id')
            ->where('patients.is_test', '=', 0)
            ->where('providers.is_test', '=', 0)
            ->where('patient_comments.comment_type', PatientComment::SECOND_SURVEY_COMMENT_TYPE)
            ->whereRaw("DATE(patient_comments.created_at) >= DATE('{$startDate}')")
            ->whereRaw("DATE(patient_comments.created_at) <= DATE('{$endDate}')")
            ->orderBy('patient_comments.created_at')
            ->each(function ($comment) use (&$data) {
                $data[] = [
                    'Provider Name' => $comment->provider_name,
                    'Provider Email' => $comment->provider_email,
                    'PR Comment' => $comment->comment,
                    'Therapist Understanding Support Rate' => $comment->metadata['therapist_understanding_support_rate'],
                    'Therapy Atmosphere Rate' => $comment->metadata['therapy_atmosphere_rate'],
                    'Therapist Openness Share Rate' => $comment->metadata['therapist_openness_share_rate'],
                    'Therapy Session After Feelings Rate' => $comment->metadata['therapy_session_after_feelings_rate'],
                    'Suggestions' => $comment->metadata['suggestions'],
                    'Appt. Survey Created At' => $comment->created_at,
                    'Date of Service' => $comment->appt_date,
                    'Patient Name' => $comment->patient_name,
                    'Patient Status' => $comment->status,
                    'Visits Count' => $comment->visits_count,
                    'EHR Link' => "https://admin.cwr.care/chart/{$comment->patient_id}",
                ];
            });

        return $data;
    }

    public static function getColumnNames(): array
    {
        return [
            'Provider Name',
            'Provider Email',
            'PR Comment',
            'Therapist Understanding Support Rate',
            'Therapy Atmosphere Rate',
            'Therapist Openness Share Rate',
            'Therapy Session After Feelings Rate',
            'Suggestions',
            'Appt. Survey Created At',
            'Date of Service',
            'Patient Name',
            'Patient Status',
            'Visits Count',
            'EHR Link',
        ];
    }
}
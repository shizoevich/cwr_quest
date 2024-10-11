<?php

namespace App\Helpers\StatisticsReport;

use App\PatientComment;

class KaiserReferralsFillingStatisticsReportHelper implements AbstractStatisticsReportHelper
{
    public static function getData(string $startDate, string $endDate): array
    {
        $data = [];

        PatientComment::query()
            ->select([
                'patient_comments.comment',
                'patient_comments.metadata',
                'patient_comments.created_at',
                'patients.id AS patient_id',
                \DB::raw("CONCAT(`patients`.`first_name`, ' ', `patients`.`last_name`) AS patient_name"),
                'providers.provider_name',
                'users.email AS provider_email',
            ])
            ->join('patients', 'patients.id', '=', 'patient_comments.patient_id')
            ->join('providers', 'providers.id', '=', 'patient_comments.provider_id')
            ->join('users', 'users.provider_id', '=', 'providers.id')
            ->where('patients.is_test', '=', 0)
            ->where('providers.is_test', '=', 0)
            ->whereRaw("DATE(patient_comments.created_at) >= DATE('{$startDate}')")
            ->whereRaw("DATE(patient_comments.created_at) <= DATE('{$endDate}')")
            ->where('patient_comments.comment_type', PatientComment::START_FILLING_REFERRAL_FORM_COMMENT_TYPE)
            ->orderBy('patient_comments.created_at')
            ->each(function ($comment) use (&$data) {
                $data[] = [
                    'Patient ID' => $comment->patient_id,
                    'Patient Name' => $comment->patient_name,
                    'Provider Name' => $comment->provider_name,
                    'Provider Email' => $comment->provider_email,
                    'Referral Type' => $comment->metadata['document_to_fill_name'],
                    'Comment' => $comment->comment,
                    'Created At' => $comment->created_at,
                    'EHR Link' => "https://admin.cwr.care/chart/{$comment->patient_id}",
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
            'Referral Type',
            'Comment',
            'Created At',
            'EHR Link',
        ];
    }
}
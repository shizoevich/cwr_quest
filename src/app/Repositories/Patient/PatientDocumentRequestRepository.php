<?php

namespace App\Repositories\Patient;

use App\Models\Patient\DocumentRequest\PatientDocumentRequest;
use App\Repositories\Patient\PatientDocumentRequestRepositoryInterface;

class PatientDocumentRequestRepository implements PatientDocumentRequestRepositoryInterface
{
    /**
     * Get count of signed and submitted patient forms.
     * @return array
     */
    public function patientFormsCount($patient): array
    {
        $baseQuery = PatientDocumentRequest::query()->select('patient_document_requests.id')
            ->where('patient_id', $patient['id'])
            ->leftJoin('patient_document_request_items', 'patient_document_requests.id', '=', 'patient_document_request_items.request_id')
            ->leftJoin('patient_form_types', 'patient_form_types.id', '=', 'patient_document_request_items.form_type_id')
            ->where('patient_form_types.visible_in_tab', 1);

        $signedFormsCountQuery = clone $baseQuery;
        $signedFormsCount = $signedFormsCountQuery->whereNotNull('patient_document_request_items.filled_at')->count();

        $submittedFormsCount = $baseQuery->count();

        $patientFormsCount = [
            'signed_forms_count' => $signedFormsCount,
            'submitted_forms_count' => $submittedFormsCount,
        ];
        return $patientFormsCount;
    }
}

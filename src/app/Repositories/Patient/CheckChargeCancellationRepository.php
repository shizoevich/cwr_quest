<?php

namespace App\Repositories\Patient;

use App\PatientDocument;
use Illuminate\Support\Facades\DB;

class CheckChargeCancellationRepository implements CheckChargeCancellationRepositoryInterface
{
    public function getPatientsWithDocumentName($statusId) {
        $query = PatientDocument::query()
            ->select('patient_documents.id as patient_document_id', 'patient_documents.aws_document_name', 'patients.id as patient_id', 'patients.charge_for_cancellation_appointment')
            ->join(DB::raw('(SELECT patient_id, MAX(id) AS max_id
                        FROM patient_documents
                        WHERE document_type_id = 2
                        GROUP BY patient_id) max_docs'),
            function($join) {
                $join->on('patient_documents.patient_id', '=', 'max_docs.patient_id')
                    ->on('patient_documents.id', '=', 'max_docs.max_id');
            })
            ->join('patients', 'patient_documents.patient_id', '=', 'patients.id')
            ->where('patients.is_test', 0);

        if ($statusId) {
            $query->where('patients.status_id', $statusId);
        }

        return $query->get();
    }

    public function updateCancellationFee($cancellationFee, $patient) {
        return $patient->update(['charge_for_cancellation_appointment' => $cancellationFee]);
    }
}

<?php

namespace App\Repositories\PatientTransfer;

use App\Models\Patient\PatientTag;
use App\Models\Patient\PatientTransfer;
use App\Patient;
use App\Repositories\PatientHasProvider\PatientHasProviderRepositoryInterface;

class PatientTransferRepository implements PatientTransferRepositoryInterface
{
    public function getActiveList(array $data): array
    {
        $patientTransfers = PatientTransfer::query()
            ->with([
                'patient',
                'patient.insurance',
                'patient.status',
                'oldProvider',
            ])
            ->active()
            ->when(!empty($data['created_at_display_depth']) && $data['created_at_display_depth'] > 0,
                function ($query) use ($data) {
                    $query->where('unassigned_at', '>', now()->subDays($data['created_at_display_depth'])->startOfDay());
                }
            )
            ->when(!empty($data['patient_created_at_display_depth']) && $data['patient_created_at_display_depth'] > 0,
                function ($query) use ($data) {
                    $query->whereHas('patient', function ($patientQuery) use ($data) {
                        $patientQuery->where('created_at', '>', now()->subDays($data['patient_created_at_display_depth'])->startOfDay());
                    });
                }
            )
            ->orderBy('unassigned_at', 'desc')
            ->get();

        return [
            'data' => $patientTransfers,
            'meta' => [
                'count' => $patientTransfers->count(),
            ],
        ];
    }

    public function transferPatient(array $data): Patient
    {
        $patientHasProviderRepository = app()->make(PatientHasProviderRepositoryInterface::class);

        $patientHasProviderRepository->deletePatientProviderRelationship([
            'patientId' => $data['patient_id'],
            'providerId' => $data['old_provider_id'],
            'reason' => $data['reason'],
        ]);

        $patientHasProviderRepository->addPatientProviderRelationship([
            'patientId' => $data['patient_id'],
            'providerId' => $data['new_provider_id'],
        ]);

        $patient = Patient::find($data['patient_id']);

        $patient->attachTag(PatientTag::getTransferringId());

        return $patient;
    }
}
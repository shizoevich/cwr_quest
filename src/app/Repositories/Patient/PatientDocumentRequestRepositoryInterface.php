<?php

namespace App\Repositories\Patient;

interface PatientDocumentRequestRepositoryInterface
{
    /**
     * Get count of signed and submitted patient forms.
     * @return array
     */
    public function patientFormsCount($patient): array;
}

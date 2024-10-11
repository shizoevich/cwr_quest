<?php

namespace App\Repositories\PatientHasProvider;

use App\Models\PatientHasProvider;

interface PatientHasProviderRepositoryInterface
{
    public function deletePatientProviderRelationship(array $data): bool;

    public function addPatientProviderRelationship(array $data): PatientHasProvider;
}
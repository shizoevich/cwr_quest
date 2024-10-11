<?php

namespace App\Repositories\Patient\PatientDocZipGenerate;

use App\Patient;

interface PatientDocZipGenerateRepositoryInterface
{
    /**
     * @param array $documentTypeData
     * @param Patient $patient
     * @return void
     */
    public function createPatientDocZip(array $documentTypeData, Patient $patient): void;

    /**
     * @param Patient $patient
     * @param string $fileName
     */
    public function getPatientDocZip(Patient $patient, string $fileName);
}

<?php

namespace App\Repositories\PatientTransfer;

use App\Patient;

interface PatientTransferRepositoryInterface
{
    public function getActiveList(array $data): array;

    public function transferPatient(array $data): Patient;
}
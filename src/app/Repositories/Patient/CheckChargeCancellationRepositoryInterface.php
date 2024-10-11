<?php

namespace App\Repositories\Patient;

interface CheckChargeCancellationRepositoryInterface
{
    public function getPatientsWithDocumentName($statusId);

    public function updateCancellationFee($cancellationFee, $patient);
}
<?php

namespace App\Repositories\Patient;

interface PreprocessedTransactionRepositoryInterface
{
    /**
     * Get count of signed and submitted patient forms.
     * @return array
     */
    public function patientTransactionCount($patient): array;
}

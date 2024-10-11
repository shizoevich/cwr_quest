<?php

namespace App\Repositories\Patient;

use App\Models\Patient\PatientPreprocessedTransaction;
use App\Models\Square\SquareTransaction;
use App\Repositories\Patient\PreprocessedTransactionRepositoryInterface;

class PreprocessedTransactionRepository implements PreprocessedTransactionRepositoryInterface
{
    /**
     * Get patient transaction count.
     * @return array
     */
    public function patientTransactionCount($patient): array
    {
        $patientTransactionCount = PatientPreprocessedTransaction::where('patient_id', $patient['id'])
            ->where('transactionable_type', SquareTransaction::class)
            ->count();

        return [
            'patient_transaction_count' => $patientTransactionCount,
        ];
    }
}

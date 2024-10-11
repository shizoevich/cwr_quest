<?php

namespace App\Repositories\Patient;

use App\Patient;
use Carbon\Carbon;

/**
 * Interface ApiRepositoryInterface
 * @package App\Repositories\Square
 */
interface PatientDocumentRepositoryInterface
{
    /**
     * @param Patient $patient
     *
     * @return Carbon|null
     */
    public function getLastDischargeDate(Patient $patient): ?Carbon;

    /**
     * Load Lucet initial assessment data.
     *
     * @param array $data
     * @return array
     */
    public function loadTridiuumInitialAssessment(array $data): array;
}

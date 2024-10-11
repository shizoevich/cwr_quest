<?php

namespace App\Repositories\PatientInsurancesProcedure;

use Illuminate\Database\Eloquent\Collection;

interface PatientInsuranceProcedureRepositoryInterface
{
    /**
     * @return Collection
     */
    public function all(): Collection;
}
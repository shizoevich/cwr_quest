<?php

namespace App\Repositories\PatientInsurancesProcedure;

use App\PatientInsuranceProcedure;
use Illuminate\Database\Eloquent\Collection;

class PatientInsuranceProcedureRepository implements PatientInsuranceProcedureRepositoryInterface
{
    /**
     * @return Collection
     */
    public function all(): Collection
    {
        return PatientInsuranceProcedure::query()
            ->orderBy('name')
            ->get();
    }
}
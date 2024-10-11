<?php

namespace App\Repositories\PatientInsurance;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PatientInsuranceRepositoryInterface
{
    /**
     * @param int|null $limit
     * @param string|null $searchQuery
     * @return LengthAwarePaginator
     */
    public function all(?int $limit, ?string $searchQuery): LengthAwarePaginator;

    public function getInsuranceList(): Collection;
}
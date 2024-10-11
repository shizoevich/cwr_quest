<?php

namespace App\Repositories\PatientInsurance;

use App\PatientInsurance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PatientInsuranceRepository implements PatientInsuranceRepositoryInterface
{
    const DEFAULT_LIMIT = 10;
    
    /**
     * @param int|null    $limit
     * @param string|null $searchQuery
     *
     * @return LengthAwarePaginator
     */
    public function all(?int $limit, ?string $searchQuery): LengthAwarePaginator
    {
        $query = PatientInsurance::whereNotNull('external_id');

        if (!empty($searchQuery)) {
            $query->where(function ($query) use ($searchQuery) {
                $query->where('insurance', 'like', "%{$searchQuery}%")
                    ->orWhere('external_id', 'like', "%{$searchQuery}%");
            });
        }

        return $query->orderBy('insurance')
            ->paginate($limit ?? self::DEFAULT_LIMIT);
    }

    public function getInsuranceList(): Collection
    {
        return PatientInsurance::query()
            ->select(['id', 'insurance'])
            ->get();
    }
}
<?php

namespace App\Repositories\EligibilityPayer;

use App\Models\EligibilityPayer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EligibilityPayerRepository implements EligibilityPayerRepositoryInterface
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
        $query = EligibilityPayer::query();

        if (!empty($searchQuery)) {
            $query->where(function ($query) use ($searchQuery) {
                $query->where('name', 'like', "%{$searchQuery}%")
                    ->orWhere('external_id', 'like', "%{$searchQuery}%");
            });
        }

        return $query->orderBy('name')
            ->paginate($limit ?? self::DEFAULT_LIMIT);
    }
}
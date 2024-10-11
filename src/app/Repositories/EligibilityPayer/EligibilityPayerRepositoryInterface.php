<?php

namespace App\Repositories\EligibilityPayer;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EligibilityPayerRepositoryInterface
{
    /**
     * @param null|int $limit
     * @param string|null $searchQuery
     * @return LengthAwarePaginator
     */
    public function all(?int $limit, ?string $searchQuery): LengthAwarePaginator;
}
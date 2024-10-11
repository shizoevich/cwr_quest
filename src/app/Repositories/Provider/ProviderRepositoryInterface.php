<?php

namespace App\Repositories\Provider;

use App\Provider;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProviderRepositoryInterface
{
    /**
     * @param int   $limit
     * @param       $searchQuery
     * @param array $filters
     *
     * @return LengthAwarePaginator
     */
    public function all(int $limit, $searchQuery, array $filters): LengthAwarePaginator;
    
    /**
     * @param Provider|null $provider
     *
     * @return string|null
     */
    public function billingPeriodName($provider): ?string;

    /**
     * @param Provider|null $provider
     *
     * @return bool
     */
    public function isBiWeeklyType($provider): bool;

    public function getTherapistCustomTimesheetOption();

    public function getPatientsByProvider(Provider $provider, array $data): Collection;
}
<?php

namespace App\Repositories\Provider\Availability;

use App\Provider;
use App\Availability;

interface ProviderAvailabilityRepositoryInterface
{
    public function getDatesFromRequest(array $requestData);

    public function getProvidersWithTotalAvailability(array $requestData);

    public function getTotalAvailabilityForPeriod(Provider $provider, $startDate, $endDate, $withUniqueTime=false);

    public function getMinimumWorkHoursForPeriod(Provider $provider, $startDate, $endDate);

    public function getTotalAvailabilityHours(array $totalAvailability);

    public function updateRemainingLength(Availability $availability);
}

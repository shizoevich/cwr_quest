<?php

namespace App\Repositories\SecretariesDashboard\NewLostPatients;

use Carbon\Carbon;

interface NewLostPatientsRepositoryInterface
{
    public function getNewPatients(Carbon $startDate, Carbon $endDate): array;

    public function getInactivePatients(Carbon $startDate, Carbon $endDate): array;

    public function getLostPatients(Carbon $startDate, Carbon $endDate): array;
}
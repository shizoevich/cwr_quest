<?php

namespace App\Repositories\SecretariesDashboard\ImportantForToday;

use Carbon\Carbon;

interface ImportantForTodayRepositoryInterface
{
    public function getAppointmentsWithoutForms(Carbon $startDate, Carbon $endDate): array;

    public function getAppointmentsWithRequiredEligibility(Carbon $startDate, Carbon $endDate): array;

    public function getAppointmentsWithDeductible(Carbon $startDate, Carbon $endDate): array;

    public function getAppointmentsWithNegativeBalance(Carbon $startDate, Carbon $endDate): array;

    public function getPatientLastAppointments(Carbon $startDate, Carbon $endDate): array;

    public function getAppointmentsWithCash(Carbon $startDate, Carbon $endDate): array;
}
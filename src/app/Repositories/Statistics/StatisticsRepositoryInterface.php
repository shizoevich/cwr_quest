<?php

namespace App\Repositories\Statistics;

interface StatisticsRepositoryInterface
{
    public function getAvailabilities($startDate, $endDate);

    public function getAvailabilitiesMapping($startDate, $endDate);

    public function getAvailabilitiesStatisticsMapping($startDate, $endDate);

    public function getAppointments($startDate, $endDate);

    public function getAppointmentsMapping($startDate, $endDate);

    public function getAppointmentsStatisticsMapping($startDate, $endDate);

    public function getProviderNewPatientsCount($providerId, $startDate, $endDate);

    public function getNewPatientsStatisticsMapping($startDate, $endDate);

    public function getProviderTransferredPatientsCount($providerId, $startDate, $endDate);

    public function getTransferredPatientsStatisticsMapping($startDate, $endDate);

    public function getRevenueStatisticsMapping($startDate, $endDate, $billingPeriod = null);

    public function getAppliedTransactions($startDate, $endDate);

    public function getAppliedTransactionsMapping($startDate, $endDate);

    public function getTotalRevenueStatisticsMapping($startDate, $endDate);

    public function getTotalStatisticsMapping($startDate, $endDate, $billingPeriod = null);

    public function getProviderPatientPayments($providerId, $startDate, $endDate);
}

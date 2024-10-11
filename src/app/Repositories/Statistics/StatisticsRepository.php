<?php

namespace App\Repositories\Statistics;

use App\Repositories\Statistics\Traits\AvailabilityStatistics;
use App\Repositories\Statistics\Traits\PatientsStatistics;
use App\Repositories\Statistics\Traits\RevenueStatistics;
use App\Provider;

class StatisticsRepository implements StatisticsRepositoryInterface
{
    use AvailabilityStatistics, PatientsStatistics, RevenueStatistics;

    public function getTotalStatisticsMapping($startDate, $endDate, $billingPeriod = null)
    {
        $availabilityStatistics = $this->getAvailabilitiesStatisticsMapping($startDate, $endDate);
        $appointmentsStatistics = $this->getAppointmentsStatisticsMapping($startDate, $endDate);
        $revenueStatistics = $this->getRevenueStatisticsMapping($startDate, $endDate, $billingPeriod);
        $totalRevenueStatistics = $this->getTotalRevenueStatisticsMapping($startDate, $endDate);

        $providers = Provider::notTest()->select('id')->get();
        $cancelledAppointmentsRateSum = 0;
        $totalStatisticsMapping = [];

        foreach($providers as $provider) {
            $providerAvailabilityStatistics = $availabilityStatistics[$provider->id] ?? [];
            $providerAppointmentsStatistics = $appointmentsStatistics[$provider->id] ?? [];
            $providerRevenueStatistics = $revenueStatistics[$provider->id] ?? [];
            $providerTotalRevenueStatistics = $totalRevenueStatistics[$provider->id] ?? [];
            $providerPatientPayments = $this->getProviderPatientPayments($provider->id, $startDate, $endDate);
            $providerNewPatientsCount = $this->getProviderNewPatientsCount($provider->id, $startDate, $endDate);
            $providerTransferredPatientsCount = $this->getProviderTransferredPatientsCount($provider->id, $startDate, $endDate);

            // add patient payments to insurance payments
            $providerTotalRevenueStatistics['total_revenue'] = isset($providerTotalRevenueStatistics['total_revenue']) 
                ? $providerTotalRevenueStatistics['total_revenue'] + $providerPatientPayments
                : $providerPatientPayments;

            $cancelledAppointmentsRateSum += $providerAppointmentsStatistics['cancelled_appointments_rate'] ?? 0;

            $totalStatisticsMapping[$provider->id] = array_merge(
                $providerAvailabilityStatistics,
                $providerAppointmentsStatistics,
                $providerRevenueStatistics,
                $providerTotalRevenueStatistics,
                [
                    'new_patients_count' => $providerNewPatientsCount,
                    'transferred_patients_count' => $providerTransferredPatientsCount,
                ]
            );
        }

        $totalCancelledAppointmentsRate = $cancelledAppointmentsRateSum / count($providers);

        foreach($totalStatisticsMapping as $key => $value) {
            $totalStatisticsMapping[$key] = array_merge($value, [
                'total_cancelled_appointments_rate' => $totalCancelledAppointmentsRate,
            ]);
        }

        return $totalStatisticsMapping;
    }
}

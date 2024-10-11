<?php

namespace App\Services\Salary;

use App\Appointment;
use App\Components\Availability\ProviderWorkHours;
use App\Models\Billing\BillingPeriod;
use App\Provider;
use App\Status;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class SalaryQuotaService
{
    const OVERTIME_MULTIPLIER = 1.5;

    public function getProvidersForSalaryQuota(): Collection
    {
        return Provider::query()
            ->select(['id','provider_name', 'work_hours_per_week'])
            ->with([
                'tariffPlan' => function ($query) {
                    $query->select(['tariffs_plans.id', 'tariffs_plans.name']);
                },
            ])
            ->orderBy('provider_name')
            ->get();
    }

    public function calculateSalary(array $data): array
    {
        $providerId = intval($data['provider_id']);
        $weeksCount = intval($data['weeks_count']);
        $billingPeriod = BillingPeriod::find($data['billing_period_id']);

        $startDate = Carbon::parse($billingPeriod->start_date)->subWeek($weeksCount - 2)->startOfDay();
        $endDate = Carbon::parse($billingPeriod->end_date)->endOfDay();

        $workHoursHelper = new ProviderWorkHours($startDate, $endDate, true, true, false, $providerId, true);
        $totalAvailabilityMapping = $workHoursHelper->getTotalMapping()[$providerId] ?? [];

        $activeCount = $totalAvailabilityMapping['activeAppointmentsCount'] ?? 0;
        $visitsCount = ($totalAvailabilityMapping['completedAppointmentsCount'] ?? 0) + ($totalAvailabilityMapping['visitCreatedAppointmentsCount'] ?? 0);
        $cancelledCount = $totalAvailabilityMapping['canceledAppointmentsCount'] ?? 0;
        $cancelledByPatientCount = $totalAvailabilityMapping['cancelledByPatientAppointmentsCount'] ?? 0;
        $cancelledByProviderCount = $totalAvailabilityMapping['cancelledByProviderAppointmentsCount'] ?? 0;
        $lastMinuteCancelCount = $totalAvailabilityMapping['lastMinuteCancelByPatientAppointmentsCount'] ?? 0;
        $patientDidNotComeCount = $totalAvailabilityMapping['patientDidNotComeAppointmentsCount'] ?? 0;
        $cancelledByOfficeCount = $totalAvailabilityMapping['cancelledByOfficeAppointmentsCount'] ?? 0;
        $remainingAvailabilityCount = $totalAvailabilityMapping['forApptsRemainingAvailabilityLength'] ?? 0;
        $remainingAvailabilityCount = floor($remainingAvailabilityCount / 60);
        $totalAppointmentsCount = $activeCount + $visitsCount + $cancelledCount + $remainingAvailabilityCount;

        $activeAvg = round($activeCount / $weeksCount, 2);
        $visitsAvg = round($visitsCount / $weeksCount, 2);
        $cancelledAvg = round($cancelledCount / $weeksCount, 2);
        $remainingAvailabilityAvg = round($remainingAvailabilityCount / $weeksCount, 2);

        $cancelledPercentage = round(($cancelledCount / $totalAppointmentsCount) * 100, 2);
        $cancelledByProviderPercentage =  round(($cancelledByProviderCount / $totalAppointmentsCount) * 100, 2);

        $totalHoursAvg = $this->getTotalHoursAvg($data, $activeAvg, $cancelledAvg, $visitsAvg, $remainingAvailabilityAvg);
        $visitsCountForSelectedBillingPeriod = $this->getVisitsCountForSelectedBillingPeriod($billingPeriod, $providerId);
        $usedQuota = $this->getUsedQuota($data, $totalHoursAvg, $visitsCountForSelectedBillingPeriod, $cancelledPercentage, $cancelledByProviderPercentage);
        [$newSalary, $oldSalary, $incentiveBonus, $reducedPayout, $incentiveVisitsCount, $overtimeCount] = $this->getSalaryData($billingPeriod, $providerId, $data, $usedQuota);

        return [
            'start_date' => $startDate->format('m/d/Y'),
            'end_date' => $endDate->format('m/d/Y'),
            'weeks_count' => $weeksCount,
            'work_hours_per_week' => intval($data['work_hours_per_week']),
            'total_hours_avg' => round($totalHoursAvg, 2),
            'visits_avg' => round($visitsAvg, 2),
            'used_quota' => $usedQuota,
            'billing_period' => Carbon::parse($billingPeriod->start_date)->format('m/d/Y') . ' - ' . Carbon::parse($billingPeriod->end_date)->format('m/d/Y'),
            'visits_count_for_billing_period' => $visitsCountForSelectedBillingPeriod,
            'old_salary' => round($oldSalary, 2),
            'new_salary' => round($newSalary, 2),
            'incentive_bonus' => round($incentiveBonus, 2),
            'incentive_visits_count' => $incentiveVisitsCount,
            'reduced_payout' => round($reducedPayout,2),
            'overtime_count' => $overtimeCount,
            'total_cancelled_percentage' => intval(__data_get($data, 'total_cancelled_percentage_kpi.value')),
            'cancelled_by_provider_percentage' => intval(__data_get($data, 'cancelled_by_provider_percentage_kpi.value')),
            'work_hours_data' => [
                'active' => [
                    'label' => 'Active Appointments',
                    'count' => $activeCount,
                    'percentage' => round(($activeCount / $totalAppointmentsCount) * 100, 2),
                    'avg' => round($activeCount / $weeksCount, 2),
                ],
                'visits' => [
                    'label' => 'Visits',
                    'count' => $visitsCount,
                    'percentage' => round(($visitsCount / $totalAppointmentsCount) * 100, 2),
                    'avg' => round($visitsCount / $weeksCount, 2),
                ],
                'cancelled' => [
                    'label' => 'Cancelled Appointments',
                    'count' => $cancelledCount,
                    'percentage' => round(($cancelledCount / $totalAppointmentsCount) * 100, 2),
                    'avg' => round($cancelledCount / $weeksCount, 2),
                ],
                'cancelled_by_patient' => [
                    'label' => 'Cancelled By Patient Appointments',
                    'count' => $cancelledByPatientCount,
                    'percentage' => round(($cancelledByPatientCount / $totalAppointmentsCount) * 100, 2),
                    'avg' => round($cancelledByPatientCount / $weeksCount, 2),
                ],
                'cancelled_by_provider' => [
                    'label' => 'Cancelled By Provider Appointments',
                    'count' => $cancelledByProviderCount,
                    'percentage' => round(($cancelledByProviderCount / $totalAppointmentsCount) * 100, 2),
                    'avg' => round($cancelledByProviderCount / $weeksCount, 2),
                ],
                'last_minute_cancel' => [
                    'label' => 'Last Minute Cancel Appointments',
                    'count' => $lastMinuteCancelCount,
                    'percentage' => round(($lastMinuteCancelCount / $totalAppointmentsCount) * 100, 2),
                    'avg' => round($lastMinuteCancelCount / $weeksCount, 2),
                ],
                'patient_did_not_come' => [
                    'label' => 'Patient Did Not Come Appointments',
                    'count' => $patientDidNotComeCount,
                    'percentage' => round(($patientDidNotComeCount / $totalAppointmentsCount) * 100, 2),
                    'avg' => round($patientDidNotComeCount / $weeksCount, 2),
                ],
                'cancelled_by_office' => [
                    'label' => 'Cancelled by Office Appointments',
                    'count' => $cancelledByOfficeCount,
                    'percentage' => round(($cancelledByOfficeCount / $totalAppointmentsCount) * 100, 2),
                    'avg' => round($cancelledByOfficeCount / $weeksCount, 2),
                ],
                'remaining_availability' => [
                    'label' => 'Remaining Availability',
                    'count' => $remainingAvailabilityCount,
                    'percentage' => round(($remainingAvailabilityCount / $totalAppointmentsCount) * 100, 2),
                    'avg' => round($remainingAvailabilityCount / $weeksCount, 2),
                ],
            ],
        ];
    }
    
    private function getTotalHoursAvg(array $data, float $activeAvg, float $cancelledAvg, float $visitsAvg, float $remainingAvailabilityAvg): float
    {
        $totalHoursAvg = 0;

        if ($data['with_active']) {
            $totalHoursAvg += $activeAvg;
        }
        if ($data['with_visits']) {
            $totalHoursAvg += $visitsAvg;
        }
        if ($data['with_cancelled']) {
            $totalHoursAvg += $cancelledAvg;
        }
        if ($data['with_availability']) {
            $totalHoursAvg += $remainingAvailabilityAvg;
        }

        return $totalHoursAvg;
    }

    private function getUsedQuota(array $data, float $totalHoursAvg, int $visitsCountForSelectedBillingPeriod, float $cancelledPercentage, float $cancelledByProviderPercentage): string
    {
        $totalCancelledPercentageForKpi = intval(__data_get($data, 'total_cancelled_percentage_kpi.value'));
        $totalCancelledPercentageKpi = __data_get($data, 'total_cancelled_percentage_kpi.kpi');
        $cancelledByProviderPercentageForKpi = intval(__data_get($data, 'cancelled_by_provider_percentage_kpi.value'));
        $cancelledByProviderPercentageKpi = __data_get($data, 'cancelled_by_provider_percentage_kpi.kpi');

        $workHoursPerWeek = intval($data['work_hours_per_week']);
        $visitsPerBillingPeriodForIncentive = intval($data['visits_per_billing_period_for_incentive']);

        $isActiveCancelledPercentageKpi = $cancelledPercentage >= $totalCancelledPercentageForKpi;
        $isActiveCancelledByProviderPercentageKpi = $cancelledByProviderPercentage >= $cancelledByProviderPercentageForKpi;
        $isActiveKpi = $isActiveCancelledPercentageKpi || $isActiveCancelledByProviderPercentageKpi;

        if ($visitsCountForSelectedBillingPeriod > $visitsPerBillingPeriodForIncentive && !$isActiveKpi) {
            return 'Incentive';
        }else if (
            $totalHoursAvg >= $workHoursPerWeek
            && (!$isActiveCancelledPercentageKpi || $totalCancelledPercentageKpi !== 'reduce_price')
            && (!$isActiveCancelledByProviderPercentageKpi || $cancelledByProviderPercentageKpi !== 'reduce_price')
        ) {
            return 'Regular';
        } else {
            return 'Reduced';
        }
    }

    private function getVisitsCountForSelectedBillingPeriod(BillingPeriod $billingPeriod, int $providerId): int
    {
        $workHoursHelper = new ProviderWorkHours(Carbon::parse($billingPeriod->start_date), Carbon::parse($billingPeriod->end_date), false, false, false, $providerId, true);
        $totalAvailabilityMapping = $workHoursHelper->getTotalMapping()[$providerId];

        return ($totalAvailabilityMapping['completedAppointmentsCount'] ?? 0) + ($totalAvailabilityMapping['visitCreatedAppointmentsCount'] ?? 0);
    }

    private function getSalaryData(BillingPeriod $billingPeriod, int $providerId, array $data, string $usedQuota): array
    {
        $completedVisitCreatedStatusesId = Status::getCompletedVisitCreatedStatusesId();

        $startUnixTimestamp = Carbon::parse($billingPeriod->start_date)->startOfDay()->timestamp;
        $endUnixTimestamp = Carbon::parse($billingPeriod->end_date)->endOfDay()->timestamp;

        $visitsPerBillingPeriodForIncentive = intval($data['visits_per_billing_period_for_incentive']);

        $count = 0;
        $overtimeCount = 0;

        $newSalary = 0;
        $oldSalary = 0;
        $incentiveBonus = 0;
        $reducedPayout = 0;
        $incentiveVisitsCount = 0;

        Appointment::query()
            ->select(['appointments.id', 'treatment_modalities.duration', 'patient_visits.is_overtime', 'patient_visits.is_cash', 'salary.fee'])
            ->join('treatment_modalities', 'treatment_modalities.id', 'appointments.treatment_modality_id')
            ->join('patient_visits', 'patient_visits.appointment_id', 'appointments.id')
            ->join('salary', function($join) {
                $join->on('salary.visit_id', '=', 'patient_visits.id')
                    ->whereNull('salary.deleted_at');
            })
            ->where('providers_id', $providerId)
            ->whereBetween('time', [$startUnixTimestamp, $endUnixTimestamp])
            ->whereIn('appointment_statuses_id', $completedVisitCreatedStatusesId)
            ->orderBy('time')
            ->each(function ($item) use (&$count, &$overtimeCount, &$oldSalary, &$newSalary, &$incentiveBonus, &$reducedPayout, &$incentiveVisitsCount, $data, $usedQuota, $visitsPerBillingPeriodForIncentive) {
                if ($item->is_overtime) {
                    $multiplier = self::OVERTIME_MULTIPLIER;
                    $overtimeCount++;
                } else {
                    $multiplier = 1;
                    $count++;
                }

                if ($item->is_cash) {
                    $cashPay = $item->fee / 100;
                    $oldSalary += $cashPay;
                    $newSalary += $cashPay;

                    return;
                }

                $prices = __data_get($data, 'prices.visit_length_' . $item->duration);

                $oldSalary += floatval($prices['regular']) * $multiplier;

                if ($usedQuota === 'Incentive' && !$item->is_overtime && $count > $visitsPerBillingPeriodForIncentive) {
                    $newSalary += floatval($prices['regular']);
                    $incentiveBonus += floatval($prices['incentive']) - floatval($prices['regular']);
                    $incentiveVisitsCount++;
                } else if ($usedQuota === 'Reduced') {
                    $newSalary += floatval($prices['reduced']) * $multiplier;
                    $reducedPayout += (floatval($prices['regular']) - floatval($prices['reduced'])) * $multiplier;
                } else {
                    $newSalary += floatval($prices['regular']) * $multiplier;
                }
            });

        return [$newSalary, $oldSalary, $incentiveBonus, $reducedPayout, $incentiveVisitsCount, $overtimeCount];
    }
}
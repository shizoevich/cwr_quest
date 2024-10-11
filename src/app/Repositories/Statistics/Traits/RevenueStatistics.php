<?php

namespace App\Repositories\Statistics\Traits;

use App\Components\Salary\Salary;
use App\Provider;
use App\PatientVisit;
use App\Models\Officeally\OfficeallyAppliedTransactionType;
use App\Appointment;
use App\PatientSquareAccount;
use Carbon\Carbon;

trait RevenueStatistics
{
    public function getRevenueStatisticsMapping($startDate, $endDate, $billingPeriod = null)
    {
        if (!($startDate instanceof Carbon)) {
            $startDate = Carbon::parse($startDate);
        }
        if (!($endDate instanceof Carbon)) {
            $endDate = Carbon::parse($endDate);
        }

        $salaryHelper = new Salary($startDate, $endDate, null, $billingPeriod);
        $salary = $salaryHelper->get()->toArray();

        $providers = Provider::notTest()->select('id')->get();
        $providerRevenueStatisticsMapping = [];

        foreach($providers as $provider) {
            $providerRevenueStatisticsMapping[$provider->id] = [
                'provider_revenue' => $this->calculateProviderRevenue($provider->id, $salary),
            ];
        }

        return $providerRevenueStatisticsMapping;
    }

    protected function calculateProviderRevenue($providerId, $salary)
    {
        $total = 0;

        $regular = __data_get($salary, 'regular.' . $providerId, []);
        foreach ($regular as $item) {
            $total += $item['amount_paid'] ?? 0;
        }
        $refundsForCompletedProgressNotes = __data_get($salary, 'refunds_for_completed_progress_notes.' . $providerId, []);
        foreach ($refundsForCompletedProgressNotes as $item) {
            $total += $item['amount_paid'] ?? 0;
        }
        $missingProgressNotes = __data_get($salary, 'missing_progress_notes.' . $providerId, []);
        foreach ($missingProgressNotes as $item) {
            $total += $item['amount_paid'] ?? 0;
        }
        $additionalCompensation = __data_get($salary, 'additional_compensation.' . $providerId, []);
        foreach ($additionalCompensation as $item) {
            $total += $item['paid_fee'] ?? 0;
        }

        return $total;
    }

    public function getAppliedTransactions($startDate, $endDate)
    {
        if (!($startDate instanceof Carbon)) {
            $startDate = Carbon::parse($startDate);
        }
        if (!($endDate instanceof Carbon)) {
            $endDate = Carbon::parse($endDate);
        }

        return PatientVisit::query()
            ->select([
                'patient_visits.*', 
                'officeally_applied_transactions.id AS transaction_id',
                'officeally_applied_transactions.applied_transaction_type_id',
                \DB::raw('officeally_applied_transactions.applied_amount / 100 AS applied_amount'),
            ])
            ->leftJoin('officeally_applied_transactions', 'officeally_applied_transactions.patient_visit_id', '=', 'patient_visits.id')
            ->join('patients', 'patients.id', '=', 'patient_visits.patient_id')
            ->join('providers', 'providers.id', '=', 'patient_visits.provider_id')
            ->where('patients.is_test', '=', 0)
            ->where('providers.is_test', '=', 0)
            ->whereDate('patient_visits.date', '>=', $startDate->toDateString())
            ->whereDate('patient_visits.date', '<=', $endDate->toDateString())
            ->get();
    }

    public function getAppliedTransactionsMapping($startDate, $endDate)
    {
        $transactions = $this->getAppliedTransactions($startDate, $endDate);
        if (is_null($transactions)) {
            return [];
        }

        return $transactions->reduce(function ($carry, $item) {
            if (!isset($carry[$item->provider_id])) {
                $carry[$item->provider_id] = [];
            }

            array_push($carry[$item->provider_id], $item);
            
            return $carry;
        }, []);
    }

    public function getTotalRevenueStatisticsMapping($startDate, $endDate)
    {
        $transactionsMapping = $this->getAppliedTransactionsMapping($startDate, $endDate);
        $totalRevenueStatisticsMapping = [];

        $insuranceTypeId = OfficeallyAppliedTransactionType::getInsurancePaymentId();

        foreach ($transactionsMapping as $key => $value) {
            $visitIds = [];
            $appliedVisitIds = [];
            $insurancePayments = 0;

            foreach ($value as $visit) {
                if (isset($visit->transaction_id)) {
                    $appliedVisitIds[] = $visit->id;
                    
                    // count only insurance payments, exclude patients payments and adjustments
                    if ($visit->applied_transaction_type_id === $insuranceTypeId) {
                        $insurancePayments += $visit->applied_amount;
                    }
                }
                
                $visitIds[] = $visit->id;
            }

            $totalRevenueStatisticsMapping[$key] = [
                'visits_count' => count(array_unique($visitIds)),
                'applied_visits_count' => count(array_unique($appliedVisitIds)),
                'total_revenue' => $insurancePayments,
            ];
        }

        return $totalRevenueStatisticsMapping;
    }

    protected function getProviderPatients($providerId, Carbon $startDate, Carbon $endDate)
    {
        $visits = PatientVisit::query()
            ->select(['patient_visits.patient_id'])
            ->join('patients', 'patients.id', '=', 'patient_visits.patient_id')
            ->where('patient_visits.provider_id', '=', $providerId)
            ->where('patients.is_test', '=', 0)
            ->whereDate('patient_visits.date', '>=', $startDate->toDateString())
            ->whereDate('patient_visits.date', '<=', $endDate->toDateString())
            ->get();

        return $visits->pluck('patient_id')->unique()->values()->all();
    }

    protected function getPatientPayments(array $patients, Carbon $startDate, Carbon $endDate)
    {
        return PatientSquareAccount::query()
            ->select([\DB::raw('square_transactions.amount_money / 100 AS patient_payment')])
            ->join('square_transactions', 'square_transactions.customer_id', '=', 'patient_square_accounts.id')
            ->whereIn('patient_square_accounts.patient_id', $patients)
            ->whereDate('square_transactions.transaction_date', '>=', $startDate->toDateString())
            ->whereDate('square_transactions.transaction_date', '<=', $endDate->toDateString())
            ->get();
    }

    public function getProviderPatientPayments($providerId, $startDate, $endDate)
    {
        if (!($startDate instanceof Carbon)) {
            $startDate = Carbon::parse($startDate);
        }
        if (!($endDate instanceof Carbon)) {
            $endDate = Carbon::parse($endDate);
        }

        $patients = $this->getProviderPatients($providerId, $startDate, $endDate);
        $patientPayments = $this->getPatientPayments($patients, $startDate, $endDate);

        return $patientPayments->reduce(function ($carry, $item) {
            return $carry + $item->patient_payment;
        }, 0);
    }
}

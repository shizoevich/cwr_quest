<?php

namespace App\Jobs\Salary;

use App\Models\Billing\BillingPeriod;
use App\Models\Provider\Salary;
use App\PatientVisit;
use App\Status;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;

class SyncSalaryData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private $billingPeriods;
    
    private $currentBillingPeriod;

    /**
     * @var int|null
     */
    private $visitCreatedId;

    /**
     * @var int|null
     */
    private $completedId;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->initBillingPeriods();
        $this->visitCreatedId = Status::getVisitCreatedId();
        $this->completedId = Status::getCompletedId();
        $this->processingVisits();
        $this->processingTimesheetVisits();
    }

    private function initBillingPeriods()
    {
        $minDate = PatientVisit::query()
            ->withTrashed()
            ->needsUpdateSalary()
            ->min('date');
        if($minDate) {
            $minDate = Carbon::parse($minDate);
            if($minDate->gt(Carbon::now())) {
                $minDate = Carbon::now();
            }
        } else {
            $minDate = Carbon::now();
        }
        $this->billingPeriods = BillingPeriod::query()
            ->where('end_date', '>=', $minDate)
            ->get();
    }
    
    private function processingTimesheetVisits()
    {
        PatientVisit::query()
            ->withTrashed()
            ->whereNotNull('salary_timesheet_visit_id')
            ->select([
                'patient_visits.id',
                'patient_visits.provider_id',
                'patient_visits.date',
                'patient_visits.reason_id',
                'patient_visits.deleted_at',
                'patient_visits.is_overtime',
                'tariffs_plans.fee_per_missing_pn',
                'patient_visits.is_telehealth',
                \DB::raw("IF(
                    `patient_visits`.`is_telehealth` = 1,
                    (
                        SELECT `telehealth_price`
                        FROM `patient_insurances_plans_procedures`
                        WHERE `tariff_plan_id` = `tariffs_plans`.`id`
                            AND `procedure_id` = `patient_visits`.`procedure_id`
                            AND `plan_id` = IF(`patient_insurances_plans`.`parent_id` IS NULL, `patient_insurances_plans`.`id`, `patient_insurances_plans`.`parent_id`)
                        LIMIT 1
                    ),
                    (
                        SELECT `price`
                        FROM `patient_insurances_plans_procedures`
                        WHERE `tariff_plan_id` = `tariffs_plans`.`id`
                            AND `procedure_id` = `patient_visits`.`procedure_id`
                            AND `plan_id` = IF(`patient_insurances_plans`.`parent_id` IS NULL, `patient_insurances_plans`.`id`, `patient_insurances_plans`.`parent_id`)
                        LIMIT 1
                    )
                ) AS fee_per_visit"),
                \DB::raw("0 AS is_progress_note_missing"),
                \DB::raw('patient_visits.date AS pn_finalized_at'),
                'providers.billing_period_type_id',
            ])
            ->join('providers', 'providers.id', '=', 'patient_visits.provider_id')
            ->join('tariffs_plans', 'tariffs_plans.id', '=', 'patient_visits.provider_tariff_plan_id')
            ->leftJoin('patient_insurances_plans', 'patient_insurances_plans.id', '=', 'patient_visits.plan_id')
            ->needsUpdateSalary()
            ->where('is_update_salary_enabled', 1)
            ->chunkById(1000, function(Collection $visits) {
                $visits->each(function ($patientVisit) {
                    $this->processingVisit($patientVisit);
                });

                // load visits again to prevent problems with updating
                PatientVisit::query()
                    ->withTrashed()
                    ->whereIn('id', $visits->pluck('id'))
                    ->each(function ($patientVisit) {
                        $patientVisit->update(['needs_update_salary' => 0]);
                    });
            }, 'patient_visits.id', 'id');
    }
    
    private function processingVisits()
    {
        PatientVisit::query()
            ->select(['id'])
            ->onlyTrashed()
            ->needsUpdateSalary()
            ->where('is_update_salary_enabled', 1)
            ->chunkById(1000, function($visits) {
                Salary::query()->whereIn('visit_id', $visits->pluck('id'))->delete();

                // load visits again to prevent problems with updating
                PatientVisit::query()
                    ->onlyTrashed()
                    ->whereIn('id', $visits->pluck('id'))
                    ->each(function ($patientVisit) {
                        $patientVisit->update(['needs_update_salary' => 0]);
                    });
            });
        
        PatientVisit::query()
            ->withTrashed()
            ->whereNull('salary_timesheet_visit_id')
            ->select([
                'patient_visits.id',
                'patient_visits.provider_id',
                'patient_visits.date',
                'patient_visits.reason_id',
                'patient_visits.deleted_at',
                'patient_visits.is_overtime',
                'tariffs_plans.fee_per_missing_pn',
                'patient_visits.is_telehealth',
                \DB::raw("IF(
                    `patient_visits`.`is_telehealth` = 1,
                    (
                        SELECT `telehealth_price`
                        FROM `patient_insurances_plans_procedures`
                        WHERE `tariff_plan_id` = `tariffs_plans`.`id`
                            AND `procedure_id` = `patient_visits`.`procedure_id`
                            AND `plan_id` = IF(`patient_insurances_plans`.`parent_id` IS NULL, `patient_insurances_plans`.`id`, `patient_insurances_plans`.`parent_id`)
                        LIMIT 1
                    ),
                    (
                        SELECT `price`
                        FROM `patient_insurances_plans_procedures`
                        WHERE `tariff_plan_id` = `tariffs_plans`.`id`
                            AND `procedure_id` = `patient_visits`.`procedure_id`
                            AND `plan_id` = IF(`patient_insurances_plans`.`parent_id` IS NULL, `patient_insurances_plans`.`id`, `patient_insurances_plans`.`parent_id`)
                        LIMIT 1
                    )
                ) AS fee_per_visit"),
                \DB::raw("IF(
                    `appointments`.`initial_assessment_id` IS NOT NULL
                        OR `appointments`.`note_on_paper` = 1
                        OR `patient_notes`.`id` IS NOT NULL
                    , 0
                    , 1
                ) AS is_progress_note_missing"),
                \DB::raw('IF(`patient_notes`.`finalized_at` IS NOT NULL, `patient_notes`.`finalized_at`, IF(`appointments`.`initial_assessment_created_at` IS NOT NULL, `appointments`.`initial_assessment_created_at`, FROM_UNIXTIME(`appointments`.`time`))) AS pn_finalized_at'),
                'providers.billing_period_type_id',
            ])
            ->join('providers', 'providers.id', '=', 'patient_visits.provider_id')
            ->join('tariffs_plans', 'tariffs_plans.id', '=', 'patient_visits.provider_tariff_plan_id')
            ->leftJoin('patient_insurances_plans', 'patient_insurances_plans.id', '=', 'patient_visits.plan_id')
            ->join('appointments', function(JoinClause $join) {
                $join->on('appointments.id', '=', 'patient_visits.appointment_id')
                    ->whereNull('appointments.deleted_at')
                    ->whereIn('appointments.appointment_statuses_id', [$this->visitCreatedId, $this->completedId]);
            })
            ->leftJoin('patient_notes', function(JoinClause $join) {
                $join->on('patient_notes.appointment_id', '=', 'appointments.id')
                    ->whereNull('patient_notes.deleted_at')
                    ->where('patient_notes.is_finalized', '=', 1);
            })
            ->needsUpdateSalary()
            ->where('is_update_salary_enabled', 1)
            ->chunkById(1000, function(Collection $visits) {
                $visits->each(function ($patientVisit) {
                    $this->processingVisit($patientVisit);
                });

                // load visits again to prevent problems with updating
                PatientVisit::query()
                    ->withTrashed()
                    ->whereIn('id', $visits->pluck('id'))
                    ->each(function ($patientVisit) {
                        $patientVisit->update(['needs_update_salary' => 0]);
                    });
            }, 'patient_visits.id', 'id');
    }
    
    /**
     * @param $visit
     */
    private function processingVisit($visit)
    {
        if($visit->deleted_at) {
            Salary::query()
                ->where('visit_id', '=', $visit->id)
                ->delete();
            return;
        }
        $visit->fee_per_missing_pn = (int)$visit->fee_per_missing_pn;
        $visit->fee_per_visit = (int)$visit->fee_per_visit;
        $salaryRecord = Salary::query()
            ->where('visit_id', '=', $visit->id) 
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
        $salaryTypeAndFee = $this->getSalaryTypeAndFee($visit, $salaryRecord);
        if(!$salaryTypeAndFee) {
            return;
        }
        $needRefund = $salaryTypeAndFee['need_refund'];
        unset($salaryTypeAndFee['need_refund']);
        $data = array_merge([
            'visit_id' => $visit->id,
            'provider_id' => $visit->provider_id,
            'date' => $visit->date,
            'billing_period_id' => $this->getBillingPeriodId($visit->date, $visit->billing_period_type_id),
        ], $salaryTypeAndFee);
        
        if($salaryRecord) {
            $salaryRecord = $salaryRecord->updateSalaryRecord($data);
        } else {
            $salaryRecord = Salary::create($data);
        }
        if($needRefund && !Salary::query()->where('visit_id', $salaryRecord->visit_id)->whereIn('type', Salary::REFUND_TYPES)->exists()) {
            $visit->is_progress_note_missing = 0;
            $salaryTypeAndFee = $this->getSalaryTypeAndFee($visit, $salaryRecord, true);
            unset($salaryTypeAndFee['need_refund']);
            $data = array_merge([
                'visit_id' => $visit->id,
                'provider_id' => $visit->provider_id,
                'date' => $visit->date,
                'billing_period_id' => $this->getBillingPeriodId($visit->pn_finalized_at, $visit->billing_period_type_id),
            ], $salaryTypeAndFee);
            $salaryRecord->updateSalaryRecord($data);
        }
    }
    
    /**
     * @param $date
     * @param $billingPeriodTypeId
     *
     * @return mixed|null
     */
    private function getBillingPeriodId($date, $billingPeriodTypeId)
    {
        $date = Carbon::parse($date)->toDateString();
        $period = $this->billingPeriods->where('type_id', '=', $billingPeriodTypeId)
            ->where('end_date', '>=', $date)
            ->where('start_date', '<=', $date)
            ->first();
        
        return optional($period)->id;
    }
    
    /**
     * @param      $visit
     * @param      $salaryRecord
     * @param bool $isRefund
     *
     * @return array|null
     */
    private function getSalaryTypeAndFee($visit, $salaryRecord, $isRefund = false)
    {
        $feeRate = $visit->is_overtime ? Salary::OVERTIME_VISITS_RATE : 1;
        /**
         * Salary before BillingPeriod::DEFAULT_START_DATE (Old salary system)
         */
        if(Carbon::parse($visit->date)->lt(Carbon::parse(BillingPeriod::DEFAULT_START_DATE))) {
            return [
                'type' => Salary::TYPE_REGULAR_VISIT,
                'fee' => (int)($visit->fee_per_visit * $feeRate),
                'paid_fee' => (int)($visit->fee_per_visit * $feeRate),
                'need_refund' => false,
            ];
        }
    
        /**
         * Visit with missing progress note
         */
        if($visit->is_progress_note_missing) {
            $data = [
                'fee' => (int)($visit->fee_per_visit * $feeRate),
                'paid_fee' => $visit->fee_per_visit
                    ? (int)($visit->fee_per_missing_pn * $feeRate)
                    : 0,
                'need_refund' => false,
            ];
            if($visit->is_telehealth) {
                $data['type'] = Salary::TYPE_TELEHEALTH_VISIT_WITH_MISSING_PROGRESS_NOTE;
            } else {
                $data['type'] = Salary::TYPE_REGULAR_VISIT_WITH_MISSING_PROGRESS_NOTE;
            }
    
            return $data;
        }
    
        /**
         * Visit with missing progress note in visit billing period
         */
        if(
            $visit->pn_finalized_at
            && $this->getBillingPeriodId($visit->pn_finalized_at, $visit->billing_period_type_id) != $this->getBillingPeriodId($visit->date, $visit->billing_period_type_id)
            && !$isRefund
        ) {
            if($salaryRecord && in_array($salaryRecord->type, [Salary::TYPE_REGULAR_VISIT, Salary::TYPE_TELEHEALTH_VISIT])) {
                return null;
            }
            $data = [
                'fee' => (int)($visit->fee_per_visit * $feeRate),
                'paid_fee' => $visit->fee_per_visit
                    ? (int)($visit->fee_per_missing_pn * $feeRate)
                    : 0,
                'need_refund' => true,
            ];
            if($visit->is_telehealth) {
                $data['type'] = Salary::TYPE_TELEHEALTH_VISIT_WITH_MISSING_PROGRESS_NOTE;
            } else {
                $data['type'] = Salary::TYPE_REGULAR_VISIT_WITH_MISSING_PROGRESS_NOTE;
            }
    
            return $data;
        }
    
        /**
         * Visit without salary record
         */
        if(!$salaryRecord) {
            $data = [
                'fee' => (int)($visit->fee_per_visit * $feeRate),
                'paid_fee' => (int)($visit->fee_per_visit * $feeRate),
                'need_refund' => false,
            ];
            if($visit->is_telehealth) {
                $data['type'] = Salary::TYPE_TELEHEALTH_VISIT;
            } else {
                $data['type'] = Salary::TYPE_REGULAR_VISIT;
            }
    
            return $data;
        }
    
        /**
         * Provider has created missing progress note in visit billing period
         */
        if(
            $visit->pn_finalized_at &&
            in_array($salaryRecord->type, Salary::MISSING_PROGRESS_NOTE_TYPES) &&
            $salaryRecord->billing_period_id == $this->getBillingPeriodId($visit->pn_finalized_at, $visit->billing_period_type_id)
        ) {
            if(Salary::isRefund($salaryRecord->type)) {
                return null;
            }
            $data = [
                'fee' => (int)($visit->fee_per_visit * $feeRate),
                'paid_fee' => (int)($visit->fee_per_visit * $feeRate),
                'need_refund' => false,
            ];
            if($visit->is_telehealth) {
                $data['type'] = Salary::TYPE_TELEHEALTH_VISIT;
            } else {
                $data['type'] = Salary::TYPE_REGULAR_VISIT;
            }
    
            return $data;
        }
    
        /**
         * Provider has created missing progress note in another billing period
         */
        if(
            $visit->pn_finalized_at &&
            in_array($salaryRecord->type, Salary::MISSING_PROGRESS_NOTE_TYPES) &&
            $salaryRecord->billing_period_id != $this->getBillingPeriodId($visit->pn_finalized_at, $visit->billing_period_type_id)
        ) {
            $fee = (int)(($salaryRecord->getOriginal('fee') - $salaryRecord->getOriginal('paid_fee')));
            $date = Carbon::parse($visit->pn_finalized_at);
            $data = [
                'fee' => $fee >= 0 ? $fee : 0,
                'paid_fee' => $fee >= 0 ? $fee : 0,
                'date' => $date->toDateString(),
                'billing_period_id' => $this->getBillingPeriodId($date, $visit->billing_period_type_id),
                'need_refund' => false,
            ];
            if($visit->is_telehealth) {
                $data['type'] = Salary::TYPE_REFUND_FOR_TELEHEALTH_VISIT_WITH_MISSING_PROGRESS_NOTE;
            } else {
                $data['type'] = Salary::TYPE_REFUND_FOR_REGULAR_VISIT_WITH_MISSING_PROGRESS_NOTE;
            }
    
            return $data;
        }
        
        if($salaryRecord && in_array($salaryRecord->type, [Salary::TYPE_REGULAR_VISIT, Salary::TYPE_TELEHEALTH_VISIT])) {
            $data = [
                'fee' => (int)($visit->fee_per_visit * $feeRate),
                'paid_fee' => (int)($visit->fee_per_visit * $feeRate),
                'need_refund' => false,
            ];
            if($visit->is_telehealth) {
                $data['type'] = Salary::TYPE_TELEHEALTH_VISIT;
            } else {
                $data['type'] = Salary::TYPE_REGULAR_VISIT;
            }
    
            return $data;
        }
        
        return null;
    }
}

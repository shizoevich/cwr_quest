<?php

namespace App\Jobs\Salary;

use App\Models\Provider\Salary;
use App\PatientVisit;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;

class UpdateSalaryDataWhenProviderTariffPlanChanged implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * @var int
     */
    private $providerId;
    /**
     * @var Carbon
     */
    private $dateFrom;
    /**
     * @var int
     */
    private $newTariffPlanId;
    
    /**
     * Create a new job instance.
     *
     * @param int    $newTariffPlanId
     * @param int    $providerId
     * @param Carbon $dateFrom
     */
    public function __construct(int $newTariffPlanId, int $providerId, Carbon $dateFrom)
    {
        $this->providerId = $providerId;
        $this->dateFrom = $dateFrom;
        $this->newTariffPlanId = $newTariffPlanId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        PatientVisit::query()
            ->select([
                'patient_visits.id',
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
            ])
            ->where('patient_visits.date', '>=',  $this->dateFrom->toDateString())
            ->where('patient_visits.provider_id', $this->providerId)
            ->join('tariffs_plans', 'tariffs_plans.id', '=', \DB::raw($this->newTariffPlanId))
            ->join('patient_insurances_plans', 'patient_insurances_plans.id', '=', 'patient_visits.plan_id')
            ->chunkById(1000, function(Collection $visits) {
                $visits->each(function($visit) {
                    $this->processingVisit($visit);
                });
                PatientVisit::query()
                    ->whereIn('id', $visits->pluck('id'))
                    ->each(function ($patientVisit) {
                        $patientVisit->update(['provider_tariff_plan_id' => $this->newTariffPlanId]);
                    });
            }, 'patient_visits.id', 'id');
    }
    
    /**
     * @param $visit
     */
    private function processingVisit($visit)
    {
        $visit->salary->each(function(Salary $salaryRecord) use ($visit) {
            $visit->fee_per_visit = (int)$visit->fee_per_visit;
            $visit->fee_per_missing_pn = (int)$visit->fee_per_missing_pn;
            if(in_array($salaryRecord->type, [Salary::TYPE_REGULAR_VISIT, Salary::TYPE_TELEHEALTH_VISIT])) {
                $salaryRecord->updateSalaryRecord([
                    'fee' => $visit->fee_per_visit,
                    'paid_fee' => $visit->fee_per_visit
                ]);
            } else if(in_array($salaryRecord->type, [Salary::TYPE_REGULAR_VISIT_WITH_MISSING_PROGRESS_NOTE, Salary::TYPE_TELEHEALTH_VISIT_WITH_MISSING_PROGRESS_NOTE])) {
                $salaryRecord->updateSalaryRecord([
                    'fee' => $visit->fee_per_visit,
                    'paid_fee' => $visit->fee_per_missing_pn,
                ]);
            } else if(in_array($salaryRecord->type, [Salary::TYPE_REFUND_FOR_REGULAR_VISIT_WITH_MISSING_PROGRESS_NOTE, Salary::TYPE_REFUND_FOR_TELEHEALTH_VISIT_WITH_MISSING_PROGRESS_NOTE])) {
                $fee = $visit->fee_per_visit - $visit->fee_per_missing_pn;
                $salaryRecord->updateSalaryRecord([
                    'fee' => $fee >= 0 ? $fee : 0,
                    'paid_fee' => $fee >= 0 ? $fee : 0,
                ]);
            }
        });
    }
}

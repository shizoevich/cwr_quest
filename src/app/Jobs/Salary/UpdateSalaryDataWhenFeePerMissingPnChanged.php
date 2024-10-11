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

class UpdateSalaryDataWhenFeePerMissingPnChanged implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * @var Carbon
     */
    private $dateFrom;
    /**
     * @var int
     */
    private $tariffPlanId;
    
    /**
     * Create a new job instance.
     *
     * @param int    $tariffPlanId
     * @param Carbon $dateFrom
     */
    public function __construct(int $tariffPlanId, Carbon $dateFrom)
    {
        $this->dateFrom = $dateFrom;
        $this->tariffPlanId = $tariffPlanId;
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
            ->join('tariffs_plans', 'tariffs_plans.id', '=', 'patient_visits.provider_tariff_plan_id')
            ->join('patient_insurances_plans', 'patient_insurances_plans.id', '=', 'patient_visits.plan_id')
            ->where('patient_visits.date', '>=',  $this->dateFrom->toDateString())
            ->where('patient_visits.provider_tariff_plan_id', '=', $this->tariffPlanId)
            ->chunkById(1000, function(Collection $visits) {
                $visits->each(function($visit) {
                    $this->processingVisit($visit);
                });
            }, 'patient_visits.id', 'id');
    }
    
    /**
     * @param $visit
     */
    private function processingVisit($visit)
    {
        $visit->salary()
            ->whereIn('type', [
                Salary::TYPE_REGULAR_VISIT_WITH_MISSING_PROGRESS_NOTE,
                Salary::TYPE_TELEHEALTH_VISIT_WITH_MISSING_PROGRESS_NOTE,
                Salary::TYPE_REFUND_FOR_REGULAR_VISIT_WITH_MISSING_PROGRESS_NOTE,
                Salary::TYPE_REFUND_FOR_TELEHEALTH_VISIT_WITH_MISSING_PROGRESS_NOTE
            ])->each(function(Salary $salaryRecord) use ($visit) {
                $visit->fee_per_visit = (int)$visit->fee_per_visit;
                $visit->fee_per_missing_pn = (int)$visit->fee_per_missing_pn;
                if(in_array($salaryRecord->type, [Salary::TYPE_REGULAR_VISIT_WITH_MISSING_PROGRESS_NOTE, Salary::TYPE_TELEHEALTH_VISIT_WITH_MISSING_PROGRESS_NOTE])) {
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

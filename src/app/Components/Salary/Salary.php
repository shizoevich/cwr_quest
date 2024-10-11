<?php

namespace App\Components\Salary;

use App\Models\Billing\BillingPeriod;
use App\Models\Provider\Salary as SalaryModel;
use App\Models\Provider\SalaryTimesheetLateCancellation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Salary
{
    /**
     * @var Carbon
     */
    private $startDate;
    /**
     * @var Carbon
     */
    private $endDate;
    /**
     * @var int|null
     */
    private $providerId;
    /**
     * @var BillingPeriod|null
     */
    private $billingPeriod;
    
    /**
     * Salary constructor.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param        $providerId
     */
    public function __construct(Carbon $startDate, Carbon $endDate, $providerId, $billingPeriod = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->providerId = $providerId;
        $this->billingPeriod = $billingPeriod;
    }
    
    /**
     * @return \Illuminate\Database\Concerns\BuildsQueries|Builder|\Illuminate\Database\Query\Builder|mixed
     */
    private function getBaseSalaryQuery()
    {
        $missingNoteTypes = implode(',', SalaryModel::MISSING_PROGRESS_NOTE_TYPES);
        $missingNoteType = SalaryModel::TYPE_REGULAR_VISIT_WITH_MISSING_PROGRESS_NOTE;

        return SalaryModel::query()
            ->select([
                'providers.provider_name',
                'salary.provider_id',
                \DB::raw("IF(`salary`.`type` IN ({$missingNoteTypes}), {$missingNoteType}, `salary`.`type`) AS type"),
                'salary.type',
//                'salary.procedure_id AS insurance_procedure_id',
                'patient_insurances_procedures.code AS procedure_code',
                'patient_insurances.insurance',
                'salary.paid_fee',
                'patient_visits.is_overtime',
                \DB::raw("IF(`parent_patient_insurances_plans`.`name` IS NOT NULL, `parent_patient_insurances_plans`.`name`, `patient_insurances_plans`.`name`) AS plan_name"),
                \DB::raw("IF(`parent_patient_insurances_plans`.`id` IS NULL, `patient_insurances_plans`.`id`, `parent_patient_insurances_plans`.`id`) AS g_plan_id"),
                \DB::raw("IF(`patient_visits`.`salary_timesheet_visit_id` IS NULL, FALSE, TRUE) AS is_created_from_timesheet"),
            ])
            ->join('providers', 'providers.id', '=', 'salary.provider_id')
            ->join('patient_visits', 'patient_visits.id', '=', 'salary.visit_id')
            ->join('patient_insurances_procedures', 'patient_insurances_procedures.id', '=', 'patient_visits.procedure_id')
            ->leftJoin('patient_insurances', 'patient_insurances.id', '=', 'patient_visits.insurance_id')
            ->leftJoin('patient_insurances_plans', 'patient_insurances_plans.id', '=', 'patient_visits.plan_id')
            ->leftJoin('patient_insurances_plans AS parent_patient_insurances_plans', 'parent_patient_insurances_plans.id', '=', 'patient_insurances_plans.parent_id')
            ->when($this->billingPeriod, function($query, $billingPeriod) {
                $query->where('billing_period_id', $billingPeriod->getKey());
            }, function($query) {
                $query->where('salary.date', '>=', $this->startDate->toDateString())
                    ->where('salary.date', '<=', $this->endDate->toDateString());
            })
            ->when($this->providerId, function(Builder $query, $providerId) {
                $query->where('salary.provider_id', '=', $providerId);
            });
    }
    
    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function get()
    {
        $data = $this->getBaseSalaryQuery()
            ->addSelect(\DB::raw("COUNT(DISTINCT `salary`.`id`) AS visits_per_month"))
            ->groupBy([
                'salary.provider_id',
                'patient_visits.insurance_id',
                'g_plan_id',
                'type',
                'salary.paid_fee',
                'patient_insurances_procedures.id',
                'is_created_from_timesheet',
                'is_overtime',
            ])
            ->orderBy('providers.provider_name')
            ->orderBy('patient_insurances.id')
            ->orderBy('plan_name')
            ->orderBy('patient_insurances_procedures.id')
            ->orderBy('type')
            ->get()
            ->transform(function(SalaryModel $item) {
                $item->amount_paid = $item->paid_fee * $item->visits_per_month;
                $item->is_telehealth = SalaryModel::isTelehealth($item['type']);
                if(SalaryModel::isProgressNoteMissing($item['type'])) {
                    $item->key = 'missing_progress_notes';
                } else if(SalaryModel::isRefund($item['type'])) {
                    $item->key = 'refunds_for_completed_progress_notes';
                } else {
                    $item->key = 'regular';
                }
                
                return $item;
            })
            ->groupBy('key')
            ->transform(function($item) {
                return $item->groupBy('provider_id');
            });
        
        $additionalCompensation = $this->getAdditionalCompensation();
        if($additionalCompensation->isNotEmpty()) {
            $data->put('additional_compensation', $additionalCompensation);
        }
        
        return $data;
    }
    
    private function getAdditionalCompensation()
    {
        $query = SalaryModel::query()
            ->when($this->billingPeriod, function($query, $billingPeriod) {
                $query->where('billing_period_id', $billingPeriod->getKey());
            }, function($query) {
                $query->where('date', '>=', $this->startDate->toDateString())
                    ->where('date', '<=', $this->endDate->toDateString());
            })
            ->when($this->providerId, function($query, $providerId) {
                $query->where('provider_id', $providerId);
            });
            
        /** @var Collection $data */
        $data = (clone $query)
            ->select([
                '*',
                \DB::raw("'' AS title")
            ])
            ->whereIn('type', SalaryModel::ADDITIONAL_COMPENSATION_TYPES)
            ->orderBy('type')
            ->orderBy('date')
            ->get()
            ->transform(function($item) {
                $item->title = SalaryModel::getTitleByType($item->type);
                $item->type_slug = SalaryModel::getSlugByType($item->type);
                $item->key = 'additional_compensation';
        
                return $item;
            });
        $lateCancellations = (clone $query)
            ->select([
                'salary.provider_id',
                'salary.type',
                \DB::raw("'" . SalaryModel::getTitleByType(SalaryModel::TYPE_CREATED_FROM_TIMESHEET_LATE_CANCELLATION) . "' AS title"),
                \DB::raw("'additional_compensation' AS `key`"),
                \DB::raw("SUM(`salary`.`paid_fee`) AS paid_fee"),
                \DB::raw("COUNT(`salary`.`id`) AS visit_count"),
                \DB::raw("NULL AS notes"),
            ])
            ->where('type', SalaryModel::TYPE_CREATED_FROM_TIMESHEET_LATE_CANCELLATION)
            ->groupBy(['provider_id', 'type'])
            ->get()
            ->transform(function($item) {
                $item->additional_data = ['visit_count' => $item->visit_count];
                $item->type_slug = SalaryModel::getSlugByType($item->type);
                
                return $item;
            });
        foreach ($lateCancellations as $item) {
            $data->push($item);
        }
        
        return $data->groupBy('provider_id');
    }
    
    private function getLateCancellations()
    {
        $lateCancellations = SalaryModel::query()
            ->select([
                'salary.provider_id',
                'salary.type',
                'salary.paid_fee',
                'salary.additional_data',
                'salary.notes',
                \DB::raw("'late_cancellation' AS `key`"),
            ])
            ->when($this->billingPeriod, function($query, $billingPeriod) {
                $query->where('billing_period_id', $billingPeriod->getKey());
            }, function($query) {
                $query->where('date', '>=', $this->startDate->toDateString())
                    ->where('date', '<=', $this->endDate->toDateString());
            })
            ->when($this->providerId, function($query, $providerId) {
                $query->where('provider_id', $providerId);
            })
            ->where('type', SalaryModel::TYPE_CREATED_FROM_TIMESHEET_LATE_CANCELLATION)
            ->orderBy('date')
            ->get();
        $timesheetIds = $lateCancellations->pluck('additional_data.salary_timesheet_late_cancellation_id');
        $timesheetData = SalaryTimesheetLateCancellation::query()
            ->select([
                'salary_timesheet_late_cancellations.id',
                'salary_timesheet_late_cancellations.date',
                'salary_timesheet_late_cancellations.amount',
                'salary_timesheet_late_cancellations.is_custom_created',
                \DB::raw("IF(`appointments`.`time` IS NOT NULL, `appointments`.`time`, UNIX_TIMESTAMP(`salary_timesheet_late_cancellations`.`date`)) AS appointment_time"),
                'patients.first_name',
                'patients.last_name',
                'patients.id AS patient_id',
                'patients.patient_id AS officeally_patient_id',
            ])
            ->leftJoin
            ('appointments', 'appointments.id', 'salary_timesheet_late_cancellations.appointment_id')
            ->join('patients', 'patients.id', 'salary_timesheet_late_cancellations.patient_id')
            ->whereIn('salary_timesheet_late_cancellations.id', $timesheetIds)
            ->get();
        $lateCancellations->transform(function($lateCancellation) use ($timesheetData) {
            $timesheetItem = $timesheetData->where('id', data_get($lateCancellation, 'additional_data.salary_timesheet_late_cancellation_id'))->first();
            if($timesheetItem) {
                $lateCancellation->is_custom_created = $timesheetItem->is_custom_created;
                $lateCancellation->patient_id = $timesheetItem->patient_id;
                $lateCancellation->external_patient_id = $timesheetItem->officeally_patient_id;
                $lateCancellation->patient_name = $timesheetItem->last_name . ', ' . $timesheetItem->first_name;
                $lateCancellation->visit_date = Carbon::createFromTimestamp($timesheetItem->appointment_time)->toDateTimeString();
                $lateCancellation->date = Carbon::parse($timesheetItem->date)->toDateTimeString();
                $lateCancellation->collected_fee = $timesheetItem->amount ? (int)$timesheetItem->amount : null;
            }
            
            return $lateCancellation;
        });
        
        return $lateCancellations->groupBy('provider_id');
    }
    
    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function getDetails()
    {
        $data = $this->getBaseSalaryQuery()
            ->addSelect([
                'patient_visits.visit_id',
                'salary.date',
                'patient_visits.date AS visit_date',
                'salary.fee',
                'patient_visits.patient_id',
                'patient_visits.pos',
                \DB::raw("CONCAT(`patients`.`last_name`, ', ', `patients`.`first_name`) AS patient_name"),
                'patients.first_name',
                'patients.last_name',
                'patients.patient_id AS pid',
            ])
            ->join('patients', 'patients.id', 'patient_visits.patient_id')
            ->groupBy([
                'salary.provider_id',
                'salary.visit_id',
                'type'
            ])
            ->orderBy('providers.provider_name')
            ->orderBy('salary.date')
            ->orderBy('salary.visit_id')
            ->get()
            ->transform(function($item) {
                $item->is_telehealth = SalaryModel::isTelehealth($item['type']);
                if(SalaryModel::isProgressNoteMissing($item['type'])) {
                    $item->key = 'missing_progress_notes';
                } else if(SalaryModel::isRefund($item['type'])) {
                    $item->key = 'refunds_for_completed_progress_notes';
                } else {
                    $item->key = 'regular';
                }
                
                return $item;
            })
            ->groupBy('key')
            ->transform(function($item) {
                return $item->groupBy('provider_id');
            });
        $lateCancellations = $this->getLateCancellations();
        if($lateCancellations->isNotEmpty()) {
            $data->put('late_cancellations', $lateCancellations);
        }
        
//        $additionalCompensation = $this->getAdditionalCompensation();
//        if($additionalCompensation->isNotEmpty()) {
//            $data->put('additional_compensation', $additionalCompensation);
//        }
        
        return $data;
    }
    
    private function getBaseSalaryData(bool $withAdditionalCompensation = true)
    {
        $data = $this->getBaseSalaryQuery()
            ->addSelect(\DB::raw("COUNT(DISTINCT `salary`.`id`) AS visits_per_billing_period"))
            ->groupBy([
                'salary.provider_id',
                'patient_visits.insurance_id',
                'g_plan_id',
                'type',
                'salary.paid_fee',
                'patient_insurances_procedures.id',
                'is_created_from_timesheet',
                'is_overtime',
            ])
            ->orderBy('providers.provider_name')
            ->orderBy('patient_insurances.id')
            ->orderBy('plan_name')
            ->orderBy('patient_insurances_procedures.id')
            ->orderBy('type')
            ->get()
            ->transform(function($item) {
                $item->amount_paid = $item->paid_fee * $item->visits_per_billing_period;
                $item->is_telehealth = SalaryModel::isTelehealth($item['type']);
                if(SalaryModel::isProgressNoteMissing($item['type'])) {
                    $item->key = 'missing_progress_notes';
                } else if(SalaryModel::isRefund($item['type'])) {
                    $item->key = 'refunds_for_completed_progress_notes';
                } else {
                    $item->key = 'regular';
                }
                
                return $item;
            });
        if($withAdditionalCompensation) {
            $additionalCompensation = $this->getAdditionalCompensation()->flatten();
            $additionalCompensation->each(function($item) use (&$data) {
                $item->amount_paid = $item->paid_fee;
                $data->push($item);
            });
            $lateCancellations = $this->getLateCancellations()->flatten();
            $lateCancellations->each(function($item) use (&$data) {
                $data->push($item);
            });
        }
        
        return $data;
    }
    
    /**
     * @return array[]
     */
    public function getForReport()
    {
        $data = $this->getBaseSalaryData();
        $preparedData = [];
        foreach ($data as $item) {
            if(!array_key_exists($item['key'], $preparedData)) {
                $preparedData[$item['key']] = [];
            }
            if(!array_key_exists($item['provider_id'], $preparedData[$item['key']])) {
                if($item['key'] !== 'additional_compensation') {
                    $preparedData[$item['key']][$item['provider_id']] = [
                        'provider_name'             => $item['provider_name'],
                        'provider_id'               => $item['provider_id'],
                        'amount_paid'               => 0,
                        'visits_per_billing_period' => 0,
                        'data' => [],
                    ];
                }
            }
            if($item['key'] === 'missing_progress_notes') {
                $key = (int)($item['paid_fee'] * 100);
                if(!isset($preparedData[$item['key']][$item['provider_id']]['data'][$key])) {
                    $preparedData[$item['key']][$item['provider_id']]['data'][$key] = [
                        'amount_paid' => 0,
                        'visits_per_billing_period' => 0,
                        'fee_per_missing_pn' => $item['paid_fee'],
                    ];
                }
                $preparedData[$item['key']][$item['provider_id']]['data'][$key]['amount_paid'] += $item['amount_paid'];
                $preparedData[$item['key']][$item['provider_id']]['data'][$key]['visits_per_billing_period'] += $item['visits_per_billing_period'];
//                    $preparedData[$item['key']][$item['provider_id']]['fee_per_missing_pn'] = $item['paid_fee'];
            } else if($item['key'] === 'additional_compensation') {
                $preparedData[$item['key']][$item['provider_id']][] = $item->toArray();
            } else {
                $preparedData[$item['key']][$item['provider_id']]['amount_paid'] += $item['amount_paid'];
                $preparedData[$item['key']][$item['provider_id']]['visits_per_billing_period'] += $item['visits_per_billing_period'];
            }
        }
        
        return $preparedData;
    }
    
    /**
     * @return array
     */
    public function getDetailsForReport()
    {
        return $this->getBaseSalaryData()
            ->groupBy('key')
            ->transform(function($item) {
                return $item->groupBy('provider_id');
            })
            ->toArray();
    }

    public static function getSalaryTotalMapping($salaryMapping)
    {
        $totalMapping = [];

        foreach ($salaryMapping as $providerId => $items) {
            $totalMapping[$providerId] = [
                'visits_per_month' => 0,
                'amount_paid' => 0,
                'overtime_visits_per_month' => 0,
                'overtime_amount_paid' => 0,
                'overtime_amount_paid_diff' => 0
            ];
            
            foreach ($items as $item) {
                $totalMapping[$providerId]['visits_per_month'] += $item['visits_per_month'];
                $totalMapping[$providerId]['amount_paid'] += $item['amount_paid'];

                if ($item['is_overtime']) {
                    $totalMapping[$providerId]['overtime_visits_per_month'] += $item['visits_per_month'];
                    $totalMapping[$providerId]['overtime_amount_paid'] += $item['amount_paid'];
                    $totalMapping[$providerId]['overtime_amount_paid_diff'] += $item['amount_paid'] - ($item['amount_paid'] / SalaryModel::OVERTIME_VISITS_RATE);
                }
            }
        }
        
        return $totalMapping;
    }

    public static function getMissingNotesTotalMapping($missingNotesMapping)
    {
        $totalMapping = [];

        foreach ($missingNotesMapping as $providerId => $items) {
            $totalMapping[$providerId] = [
                'data' => [],
                'visits_per_month' => 0,
                'amount_paid' => 0,
                'overtime_visits_per_month' => 0,
                'overtime_amount_paid' => 0,
            ];
            
            foreach ($items as $item) {
                $key = (int) ($item['paid_fee'] * 100);
                if (!isset($totalMapping[$providerId]['data'][$key])) {
                    $totalMapping[$providerId]['data'][$key] = [
                        'amount_paid' => 0,
                        'overtime_amount_paid' => 0,
                        'visits_per_month' => 0,
                        'overtime_visits_per_month' => 0,
                        'fee_per_visit' => $item['paid_fee']
                    ];
                }

                $totalMapping[$providerId]['data'][$key]['amount_paid'] += (float) $item['amount_paid'];
                $totalMapping[$providerId]['data'][$key]['visits_per_month'] += (int) $item['visits_per_month'];

                $totalMapping[$providerId]['visits_per_month'] += $item['visits_per_month'];
                $totalMapping[$providerId]['amount_paid'] += $item['amount_paid'];

                if ($item['is_overtime']) {
                    $totalMapping[$providerId]['data'][$key]['overtime_visits_per_month'] += (int) $item['visits_per_month'];
                    $totalMapping[$providerId]['data'][$key]['overtime_amount_paid'] += (float) $item['amount_paid'];

                    $totalMapping[$providerId]['overtime_visits_per_month'] += $item['visits_per_month'];
                    $totalMapping[$providerId]['overtime_amount_paid'] += $item['amount_paid'];
                }
            }

            $totalMapping[$providerId]['data'] = array_values($totalMapping[$providerId]['data']);
        }
        
        return $totalMapping;
    }
}
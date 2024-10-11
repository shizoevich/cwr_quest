<?php

namespace App\Jobs\Salary;

use App\Models\Provider\SalaryTimesheetVisit;
use App\PatientVisit;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;

class CalculateOvertime implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    const VISIT_PER_WEEK_LIMIT = 40;
    
    const VISIT_PER_DAY_LIMIT = 8;
    
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
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Carbon $startDate, Carbon $endDate, $providerId = null)
    {
        $this->startDate = $startDate->startOfWeek();
        $this->endDate = $endDate->endOfWeek();
        $this->providerId = $providerId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /**
         * @var array $visitsData
         * @example
         * [
         *   '<provider id>' => [
         *       '<start of week>_<end of week>' => [
         *           '<date>' => [<visit ids>],
         *       ]
         *   ]
         * ]
         */
        $visitsData = [];
        
        PatientVisit::query()
            ->select([
                'patient_visits.id',
                'patient_visits.date',
                'patient_visits.provider_id',
            ])
            ->when($this->providerId, function($query, $providerId) {
                $query->where('patient_visits.provider_id', $providerId);
            })
            ->where('patient_visits.date', '>=', $this->startDate->toDateString())
            ->where('patient_visits.date', '<=', $this->endDate->toDateString())
            ->join('appointments', 'appointments.id', 'patient_visits.appointment_id')
            ->orderBy('appointments.time')
            ->each(function($item) use (&$visitsData) {
                $date = Carbon::parse($item->date);
                $weekKey = sprintf('%s_%s', $date->copy()->startOfWeek()->toDateString(), $date->copy()->endOfWeek()->toDateString());
                $visitsData[$item->provider_id][$weekKey][$date->toDateString()][] = $item->id;
            });
        
        $data = $this->searchOvertime($visitsData);
        
        if (!empty($data['regular_visits'])) {
            SalaryTimesheetVisit::query()
                ->whereIn('visit_id', $data['regular_visits'])
                ->update([
                    'is_overtime' => false,
                ]);
            PatientVisit::query()
                ->whereIn('id', $data['regular_visits'])
                ->each(function ($patientVisit) {
                    $patientVisit->update([
                        'is_overtime' => false,
                        'needs_update_salary' => true,
                    ]);
                });

        }
        
        if (!empty($data['overtime'])) {
            SalaryTimesheetVisit::query()
                ->whereIn('visit_id', $data['overtime'])
                ->update([
                    'is_overtime' => true,
                ]);
            PatientVisit::query()
                ->whereIn('id', $data['overtime'])
                ->each(function ($patientVisit) {
                    $patientVisit->update([
                        'is_overtime' => true,
                        'needs_update_salary' => true,
                    ]);
                });
        }

        \Bus::dispatchNow(new SyncSalaryData());
    }
    
    private function searchOvertime(array $visitsData): array
    {
        $result = [
            'overtime' => [],
            'regular_visits' => [],
        ];
        foreach ($visitsData as $visitsByWeek) {
            foreach ($visitsByWeek as $visitsByDay) {
                $visitsByWeek = [];
                $oldOvertimeCount = count($result['overtime']);
                $oldRegularCount = count($result['regular_visits']);

                foreach ($visitsByDay as $visitIds) {
                    $visitsByWeek = $this->mergeVisitIds($visitsByWeek, $visitIds);

                    $additionalOffset = 0;
                    $currentWeekRegularCount = count($result['regular_visits']) - $oldRegularCount;
                    $visitIdsCount = count($visitIds);

                    if (($currentWeekRegularCount + $visitIdsCount) > self::VISIT_PER_WEEK_LIMIT) {
                        if ($currentWeekRegularCount < self::VISIT_PER_WEEK_LIMIT && $visitIdsCount > self::VISIT_PER_DAY_LIMIT) {
                            $additionalOffset = max($currentWeekRegularCount + self::VISIT_PER_DAY_LIMIT - self::VISIT_PER_WEEK_LIMIT, 0);
                        } else {
                            $additionalOffset = $currentWeekRegularCount + $visitIdsCount - self::VISIT_PER_WEEK_LIMIT;
                        }
                    }

                    if ((self::VISIT_PER_DAY_LIMIT - $additionalOffset) < 0) {
                        $result['overtime'] = $this->mergeVisitIds($result['overtime'], $visitIds);
                    } else if ($visitIdsCount > self::VISIT_PER_DAY_LIMIT) {
                        $result['overtime'] = $this->mergeVisitIds($result['overtime'], array_slice($visitIds, self::VISIT_PER_DAY_LIMIT - $additionalOffset));
                        $result['regular_visits'] = $this->mergeVisitIds($result['regular_visits'], array_slice($visitIds, 0, self::VISIT_PER_DAY_LIMIT - $additionalOffset));
                    } else {
                        $result['regular_visits'] = $this->mergeVisitIds($result['regular_visits'], $visitIds);
                    }
                }

                $visitPerCurrentWeekLimit = self::VISIT_PER_WEEK_LIMIT + count($result['overtime']) - $oldOvertimeCount;

                if (count($visitsByWeek) > $visitPerCurrentWeekLimit) {
                    $overtimeVisits = array_slice($visitsByWeek, $visitPerCurrentWeekLimit);

                    //delete overtime visits from regular visits array
                    $result['regular_visits'] = array_diff($result['regular_visits'], $overtimeVisits);
                    $result['overtime'] = $this->mergeVisitIds($result['overtime'], $overtimeVisits);
                }
            }
        }
        
        return $result;
    }
    
    /**
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    private function mergeVisitIds(array $array1, array $array2): array
    {
        return array_values(array_unique(array_merge($array1, $array2)));
    }
}

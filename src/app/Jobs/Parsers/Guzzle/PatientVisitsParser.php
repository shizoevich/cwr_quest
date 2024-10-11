<?php

namespace App\Jobs\Parsers\Guzzle;

use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Jobs\DeleteVisits;
use App\Jobs\Salary\CalculateOvertime;
use App\Models\Billing\BillingPeriod;
use App\Models\Billing\BillingPeriodType;
use App\Models\Provider\Salary;
use App\Option;
use App\PatientVisit;
use App\PatientVisitStatus;
use Carbon\Carbon;

/**
 * Class PatientVisitsParser
 * @package App\Jobs\Parsers\Guzzle
 */
class PatientVisitsParser extends AbstractParser
{
    private $parsedVisits  = [];
    private $visitStatuses = [];
    
    private   $options;
    protected $isSalaryParser;
    /**
     * @var bool
     */
    private $deleteVisits;
    
    /**
     * Create a new job instance.
     *
     * @param array $options
     * @param bool  $isSalaryParser
     * @param bool  $deleteVisits
     */
    public function __construct(
        array $options,
        $isSalaryParser = false,
        $deleteVisits = true
    ) {
        $this->options = $options;
        $this->isSalaryParser = $isSalaryParser;
        $this->deleteVisits = $deleteVisits;
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handleParser()
    {
        $parsedAt = Carbon::now()->timestamp;
        $visits = $this->getVisits();
        if ($visits === null) {
            return;
        }
        foreach ($visits as $visit) {
            $visitData = [
                'visit_id'  => $visit['id'],
                'is_paid'   => 0,
                'date'      => Carbon::parse($visit['cell'][5])->toDateString(),
            ];
            if($this->deleteVisits) {
                $visitData['parsed_at'] = $parsedAt;
            }
            $statusName = $visit['cell'][9];

            if (!array_key_exists($statusName, $this->visitStatuses)) {
                $this->visitStatuses[$statusName] = PatientVisitStatus::firstOrCreate(['name' => $statusName]);
            }
            $visitInfo['status_id'] = $this->visitStatuses[$statusName]->id;
            $patientVisit = PatientVisit::where('visit_id', $visitData['visit_id'])->withTrashed()->first();
            if ($patientVisit) {
                if ($patientVisit->trashed()) {
                    $patientVisit->restore();
                }
                $patientVisit->update($visitData);
            } else {
                $patientVisit = PatientVisit::create($visitData);
                \Bus::dispatchNow(new PatientVisitsInfoParser([$patientVisit->visit_id], false));
            }
            
            $this->parsedVisits[] = $visitData['visit_id'];
        }
        if($this->deleteVisits && (!empty($this->parsedVisits) || (key_exists('visit_id', $this->options) && !is_null($this->options['visit_id'])))) {
            \Bus::dispatchNow(new DeleteVisits($parsedAt, $this->options));
        }
    
        if(!key_exists('only-visits', $this->options) || !$this->options['only-visits']) {
            \Bus::dispatchNow(new PatientVisitsInfoParser($this->parsedVisits, $this->isSalaryParser));
        }
        $dateRange = $this->getDateRange();
        \Bus::dispatchNow(new CalculateOvertime($dateRange['start_date']->startOfWeek(), $dateRange['end_date']->endOfWeek()));

        //check and delete dublicates in salary if visited is deleted
        $previousBillingPeriodStartDate = BillingPeriod::getPrevious(BillingPeriodType::TYPE_BI_WEEKLY);
        $currentBillingPeriodEndDate = BillingPeriod::getCurrent(BillingPeriodType::TYPE_BI_WEEKLY);
       
        $onlyTrashedVisits = PatientVisit::onlyTrashed()
            ->whereNull('visit_id')
            ->whereNull('appointment_id')
            ->whereBetween('date', [$previousBillingPeriodStartDate->start_date, $currentBillingPeriodEndDate->end_date]) 
            ->get();

        foreach($onlyTrashedVisits as $trashedVisit)
        {
          Salary::where('visit_id',$trashedVisit->id)->update(['deleted_at' => Carbon::now()]);
        }
    }
    
    private function getDateRange(): array
    {
        if (key_exists('month', $this->options) && !is_null($this->options['month'])) {
            $date = Carbon::createFromFormat('m/d/Y', $this->options['month']);
            $startDate = $date->startOfMonth();
            $endDate = $date->copy()->endOfMonth();
        } else if (key_exists('start_date',
                $this->options) && !is_null($this->options['start_date']) && key_exists('end_date',
                $this->options) && !is_null($this->options['end_date'])) {
            $startDate = Carbon::parse($this->options['start_date']);
            $endDate = Carbon::parse($this->options['end_date']);
        } else if (key_exists('date', $this->options) && !is_null($this->options['date'])) {
            $date = Carbon::parse($this->options['date']);
            $startDate = $date->startOfDay();
            $endDate = $date->copy()->addDay();
        } else {
            $startDate = Carbon::now()->startOfMonth()->subDays(5);
            $endDate = Carbon::now();
        }
        
        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
    
    /**
     * Copied from old parser
     *
     * @return bool|mixed|null
     */
    private function getVisits()
    {
        $officeAlly = app()->make(OfficeAllyHelper::class)(Option::OA_ACCOUNT_3);
        
        if (key_exists('visit_id', $this->options) && !is_null($this->options['visit_id'])) {
            return $officeAlly->getVisitListById($this->options['visit_id']);
        }
        $dateRange = $this->getDateRange();
        
        return $officeAlly->getVisitListByDateRange($dateRange['start_date'], $dateRange['end_date']);
    }
}

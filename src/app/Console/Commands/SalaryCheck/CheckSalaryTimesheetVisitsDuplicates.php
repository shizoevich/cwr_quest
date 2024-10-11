<?php

namespace App\Console\Commands\SalaryCheck;

use App\Models\Billing\BillingPeriod;
use App\Models\Billing\BillingPeriodType;
use App\Models\Provider\Salary;
use App\Provider;
use Illuminate\Console\Command;

class CheckSalaryTimesheetVisitsDuplicates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-salary:check-salary-timesheet-visits-duplicates';
    /** 
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check duplicate visits for salary timesheets';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $billingPeriod = BillingPeriod::getPrevious(BillingPeriodType::TYPE_BI_WEEKLY);

        $providers = Provider::query()
            ->select(['id', 'provider_name'])
            ->with([
                'salaryTimesheetVisits' => function ($query) use ($billingPeriod) {
                    $query->where('billing_period_id', $billingPeriod->id);
                },
            ])
            ->where('billing_period_type_id', BillingPeriodType::getBiWeekly()->id)
            ->orderBy('provider_name')
            ->get();

        foreach ($providers as $provider) {
            $data = [];

            $provider->salaryTimesheetVisits->each(function ($item) use (&$data, $provider) {
                $date = $item->date;
                $patientId = $item->patient_id;

                if (array_key_exists($date, $data) && in_array($patientId, $data[$date])) {
                    $this->warn('Warning! Duplicate visit in salary timesheet. Provider: ' . $provider->provider_name . '; Date: ' . $date . '; Pt. id: ' . $patientId);
                }

                $data[$item->date][] = $patientId;
            });

            $this->info('Provider: ' . $provider->provider_name . ' - checked');
        }
    }
}

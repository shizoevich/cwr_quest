<?php

namespace App\Console\Commands\SalaryCheck;

use App\Models\Billing\BillingPeriod;
use App\Models\Billing\BillingPeriodType;
use App\Models\Provider\Salary;
use App\Provider;
use Illuminate\Console\Command;

class CompareSalaryTimesheetVisitsAndSalary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-salary:compare-salary-timesheet-visits';
    /** 
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compare visits between "Salary Timesheet" and "Service Payouts" pages';
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
            ->withCount([
                'salary' => function ($query) use ($billingPeriod) {
                    $query->where('billing_period_id', $billingPeriod->id)
                        ->whereNotNull('visit_id')
                        ->whereNotIn('type', Salary::REFUND_TYPES);
                },
                'salaryTimesheetVisits' => function ($query) use ($billingPeriod) {
                    $query->where('billing_period_id', $billingPeriod->id);
                },
            ])
            ->where('billing_period_type_id', BillingPeriodType::getBiWeekly()->id)
            ->orderBy('provider_name')
            ->get();

        foreach ($providers as $provider) {
            $salaryVisitsCount = $provider->salary_count;
            $salaryTimesheetVisitsCount = $provider->salary_timesheet_visits_count;

            $this->info('Provider: ' . $provider->provider_name . '; Salary visits count - ' . $salaryVisitsCount . '; Timesheet visits count: ' . $salaryTimesheetVisitsCount);

            if ($salaryVisitsCount !== $salaryTimesheetVisitsCount) {
                $this->warn('Warning! Different count of visits between salary and timesheet for provider: ' . $provider->provider_name);
            }
        }
    }
}

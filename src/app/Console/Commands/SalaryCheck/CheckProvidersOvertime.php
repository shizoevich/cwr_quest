<?php

namespace App\Console\Commands\SalaryCheck;

use App\Models\Billing\BillingPeriod;
use App\Models\Billing\BillingPeriodType;
use App\Models\Provider\Salary;
use App\PatientVisit;
use App\Provider;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckProvidersOvertime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-salary:check-provider-overtime';
    /** 
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check providers overtime';
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

        $startDate = Carbon::parse($billingPeriod->start_date)->toDateString();
        $endDate = Carbon::parse($billingPeriod->end_date)->toDateString();

        $weekSpans = [
            [
                $startDate,
                Carbon::parse($billingPeriod->start_date)->subDay()->addWeek()->toDateString(),
            ],
            [
                Carbon::parse($billingPeriod->start_date)->addWeek()->toDateString(),
                $endDate,
            ],
        ];

        $providers = Provider::query()
            ->select(['id', 'provider_name'])
            ->with([
                'visits'  => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('date', [$startDate, $endDate]);
                },
                'salaryTimesheetVisits'  => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('date', [$startDate, $endDate]);
                },
            ])
            ->where('billing_period_type_id', BillingPeriodType::getBiWeekly()->id)
            ->orderBy('provider_name')
            ->get();

        $this->info(PHP_EOL . '--------------PatientVisit--------------' . PHP_EOL);

        foreach ($providers as $provider) {
            $totalOvertime = 0;

            foreach ($weekSpans as $span) {
                $workHours = [];

                $provider->visits->where('date', '>=', $span[0])->where('date', '<=', $span[1])->each(function ($item) use (&$workHours) {
                    if (empty($workHours[$item->date])) {
                        $workHours[$item->date] = 1;
                    } else {
                        $workHours[$item->date] += 1;
                    }
                });

                foreach ($workHours as &$workHoursCount) {
                    if ($workHoursCount > 8) {
                        $totalOvertime += $workHoursCount - 8;
                        $workHoursCount = 8;
                    }
                }

                $sumWorkHoursWithoutDailyOvertime = array_sum($workHours);

                if ($sumWorkHoursWithoutDailyOvertime > 40) {
                    $totalOvertime += $sumWorkHoursWithoutDailyOvertime - 40;
                }
            }

            $visitsOvertimeCount = $provider->visits->where('is_overtime', 1)->count();

            if ($visitsOvertimeCount !== $totalOvertime) {
                $this->warn('Warning! Provider '. $provider->provider_name . ' overtime must be ' . $totalOvertime . ' instead of ' . $visitsOvertimeCount);
            }

            $this->info('Provider: ' . $provider->provider_name . '; Overtime: ' . $totalOvertime);
        }

        $this->info(PHP_EOL . '--------------SalaryTimesheetVisit--------------' . PHP_EOL);

        foreach ($providers as $provider) {
            $totalOvertime = 0;

            foreach ($weekSpans as $span) {
                $workHours = [];

                $provider->salaryTimesheetVisits->where('date', '>=', $span[0])->where('date', '<=', $span[1])->each(function ($item) use (&$workHours) {
                    if (empty($workHours[$item->date])) {
                        $workHours[$item->date] = 1;
                    } else {
                        $workHours[$item->date] += 1;
                    }
                });

                foreach ($workHours as &$workHoursCount) {
                    if ($workHoursCount > 8) {
                        $totalOvertime += $workHoursCount - 8;
                        $workHoursCount = 8;
                    }
                }

                $sumWorkHoursWithoutDailyOvertime = array_sum($workHours);

                if ($sumWorkHoursWithoutDailyOvertime > 40) {
                    $totalOvertime += $sumWorkHoursWithoutDailyOvertime - 40;
                }
            }

            $visitsOvertimeCount = $provider->salaryTimesheetVisits->where('is_overtime', 1)->count();

            if ($visitsOvertimeCount !== $totalOvertime) {
                $this->warn('Warning! Provider '. $provider->provider_name . ' overtime must be ' . $totalOvertime . ' instead of ' . $visitsOvertimeCount);
            }

            $this->info('Provider: ' . $provider->provider_name . '; Overtime: ' . $totalOvertime);
        }
    }
}

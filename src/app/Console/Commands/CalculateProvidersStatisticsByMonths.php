<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Billing\BillingPeriod;
use App\Models\Billing\BillingPeriodType;
use Carbon\Carbon;

class CalculateProvidersStatisticsByMonths extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'providers-statistics:calculate-by-months {--start-date=} {--end-date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $startDate = null;
        $endDate = null;
        if ($this->option('start-date') && $this->option('end-date')) {
            $startDate = Carbon::parse($this->option('start-date'));
            $endDate = Carbon::parse($this->option('end-date'));
        } else if ($this->option('start-date') || $this->option('end-date')) {
            $temp = $this->option('start-date') ?? $this->option('end-date');
            $startDate = Carbon::parse($temp)->startOfMonth();
            $endDate = Carbon::parse($temp)->endOfMonth();
        } else {
            $startDate = Carbon::today()->startOfMonth();
            $endDate = Carbon::today()->endOfMonth();
        }

        $biWeeklyTypeId = BillingPeriodType::getBiWeekly()->getKey();
        $monthlyBillingPeriod = BillingPeriodType::getMonthly()->getKey();

        BillingPeriod::query()
            ->where('billing_periods.start_date', '>=', $startDate->toDateString())
            ->where('billing_periods.start_date', '<=', $endDate->toDateString())
            ->where('billing_periods.type_id', $biWeeklyTypeId)
            ->each(function ($period) use ($biWeeklyTypeId) {
                // dump('BI-WEEKLY: ' . $period->start_date . '-' . $period->end_date);
                $this->call('providers-statistics:calculate', [
                    '--period-date' => $period->start_date, 
                    '--period-type-id' => $biWeeklyTypeId
                ]);
            });

        BillingPeriod::query()
            ->where('billing_periods.start_date', '>=', $startDate->toDateString())
            ->where('billing_periods.start_date', '<=', $endDate->toDateString())
            ->where('billing_periods.type_id', $monthlyBillingPeriod)
            ->each(function ($period) use ($monthlyBillingPeriod) {
                // dump('MONTHLY: ' . $period->start_date . '-' . $period->end_date);
                $this->call('providers-statistics:calculate', [
                    '--period-date' => $period->start_date, 
                    '--period-type-id' => $monthlyBillingPeriod
                ]);
            });
    }
}

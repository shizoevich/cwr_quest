<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Statistics\StatisticsRepositoryInterface;
use App\Models\Billing\BillingPeriod;
use App\Models\Billing\BillingPeriodType;
use App\Models\ProviderStatistics;
use Carbon\Carbon;

class CalculateProvidersStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'providers-statistics:calculate {--period-date=} {--period-type-id=} {--prev}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $statisticsRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(StatisticsRepositoryInterface $statisticsRepository)
    {
        parent::__construct();
        $this->statisticsRepository = $statisticsRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('period-date')) {
            $date = Carbon::parse($this->option('period-date'));

            if ($this->option('period-type-id')) {
                $period = BillingPeriod::getBillingPeriodByDate($date, $this->option('period-type-id'));
                $this->updateBillingPeriodStatistics($period);
                return;
            }

            $biWeeklyBillingPeriod = BillingPeriod::getBillingPeriodByDate($date, BillingPeriodType::getBiWeekly()->getKey());
            $monthlyBillingPeriod = BillingPeriod::getBillingPeriodByDate($date, BillingPeriodType::getMonthly()->getKey());
            $this->updateBillingPeriodStatistics($biWeeklyBillingPeriod);
            $this->updateBillingPeriodStatistics($monthlyBillingPeriod);
            return;
        }

        if ($this->option('period-type-id')) {
            $billingPeriodType = BillingPeriodType::find($this->option('period-type-id'));

            if ($this->option('prev')) {
                $period = BillingPeriod::getPrevious($billingPeriodType->name);
                $this->updateBillingPeriodStatistics($period);
                return;
            }

            $period = BillingPeriod::getCurrent($billingPeriodType->name);
            $this->updateBillingPeriodStatistics($period);
            return;
        }

        $biWeeklyBillingPeriod = null;
        $monthlyBillingPeriod = null;
        if ($this->option('prev')) {
            $biWeeklyBillingPeriod = BillingPeriod::getPrevious(BillingPeriodType::TYPE_BI_WEEKLY);
            $monthlyBillingPeriod = BillingPeriod::getPrevious(BillingPeriodType::TYPE_MONTHLY);
        } else {
            $biWeeklyBillingPeriod = BillingPeriod::getCurrent(BillingPeriodType::TYPE_BI_WEEKLY);
            $monthlyBillingPeriod = BillingPeriod::getCurrent(BillingPeriodType::TYPE_MONTHLY);
        }

        $this->updateBillingPeriodStatistics($biWeeklyBillingPeriod);
        $this->updateBillingPeriodStatistics($monthlyBillingPeriod);
    }

    protected function updateBillingPeriodStatistics(BillingPeriod $period = null)
    {
        if (!isset($period)) {
            return;
        }

        $statistics = $this->statisticsRepository->getTotalStatisticsMapping($period->start_date, $period->end_date, $period);
        foreach($statistics as $key => $value) {
            $stats = array_merge($value, [
                'provider_id' => $key,
                'billing_period_id' => $period->id,
            ]);
            ProviderStatistics::updateOrCreate([
                'provider_id' => $key,
                'billing_period_id' => $period->id,
            ], $stats);
        }
    }
}

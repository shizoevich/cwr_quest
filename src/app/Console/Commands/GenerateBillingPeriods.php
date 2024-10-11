<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Billing\BillingPeriod;
use App\Models\Billing\BillingPeriodType;
use Carbon\Carbon;

class GenerateBillingPeriods extends Command
{
    const MONTHS = 6;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing-periods:generate';

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
        $types = BillingPeriodType::all();
        $this->generateBiWeekly($types->where('name', '=', BillingPeriodType::TYPE_BI_WEEKLY)->first());
        $this->generateMonthly($types->where('name', '=', BillingPeriodType::TYPE_MONTHLY)->first());
    }
    
    private function generateBiWeekly(BillingPeriodType $type)
    {
        $startDate = $type->periods()->max('end_date');
        if ($startDate) {
            $startDate = Carbon::parse($startDate)->addDay();
            $diff = Carbon::now()->diffInMonths($startDate, false);
            if ($diff >= self::MONTHS) {
                return;
            } else if ($diff < 0) {
                $endDate = Carbon::now()->addMonths(self::MONTHS);
            } else {
                $endDate = $startDate->copy()->addMonths(self::MONTHS - $diff);
            }
        } else {
            $startDate = Carbon::parse(BillingPeriod::DEFAULT_START_DATE);
            $endDate = Carbon::now()->addMonths(self::MONTHS);
        }
        $startDate->startOfWeek();
        $endDate->endOfWeek();
        
        while($startDate->lt($endDate)) {
            $start = $startDate->copy();
            $startDate->addDays(13);
            $type->periods()->updateOrCreate([
                'start_date' => $start->toDateString(),
                'end_date' => $startDate->toDateString(),
            ]);
            $startDate->addDay();
        }
    }
    
    private function generateMonthly(BillingPeriodType $type)
    {
        $startDate = $type->periods()->max('end_date');
        if ($startDate) {
            $startDate = Carbon::parse($startDate)->endOfMonth()->addDay();
            $diff = Carbon::now()->diffInMonths($startDate, false);
            if ($diff >= self::MONTHS) {
                return;
            } else if ($diff < 0) {
                $endDate = Carbon::now()->addMonths(self::MONTHS);
            } else {
                $endDate = $startDate->copy()->addMonths(self::MONTHS - $diff);
            }
        } else {
            $startDate = Carbon::parse(BillingPeriod::DEFAULT_START_DATE);
            $endDate = Carbon::now()->addMonths(self::MONTHS);
        }
        $startDate->startOfMonth();
        $endDate->endOfMonth();
        
        while($startDate->lt($endDate)) {
            $type->periods()->updateOrCreate([
                'start_date' => $startDate->toDateString(),
                'end_date' => $startDate->copy()->endOfMonth()->toDateString(),
            ]);
            $startDate->addMonth();
        }
    }
}

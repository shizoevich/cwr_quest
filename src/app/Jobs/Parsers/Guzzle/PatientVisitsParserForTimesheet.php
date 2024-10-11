<?php

namespace App\Jobs\Parsers\Guzzle;

use App\Models\Billing\BillingPeriod;
use App\Models\Billing\BillingPeriodType;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PatientVisitsParserForTimesheet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        BillingPeriodType::query()->each(function(BillingPeriodType $type) use (&$startDate, &$endDate) {
            $billingPeriod = BillingPeriod::getCurrent($type->name);
            if(Carbon::parse($billingPeriod->end_date)->isSameDay(Carbon::today())) {
                \Bus::dispatchNow(new PatientVisitsParser([
                    'full-time' => false,
                    'only-visits' => false,
                    'month' => null,
                    'date' => null,
                    'start_date' => $billingPeriod->start_date,
                    'end_date' => $billingPeriod->end_date,
                ]));
            }
        });
    }
}

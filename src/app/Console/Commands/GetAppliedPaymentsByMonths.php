<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Jobs\Parsers\Guzzle\AppliedPaymentsParser;

class GetAppliedPaymentsByMonths extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'applied-payments:get-by-months {--start-date=} {--end-date=}';

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

        foreach (CarbonPeriod::create($startDate, '1 month', $endDate) as $date) {
            $job = (new AppliedPaymentsParser($date, $date->copy()->endOfMonth(), AppliedPaymentsParser::APPLIED_PAYMENTS))->onQueue('payments');
            dispatch($job);
        }
    }
}

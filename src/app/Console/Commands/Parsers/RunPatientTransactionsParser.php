<?php

namespace App\Console\Commands\Parsers;

// use App\Jobs\Parsers\Guzzle\PaymentsParser;
use App\Jobs\Patients\CalculatePatientBalance;
use App\Jobs\Square\GetLocations;
use App\Jobs\Square\GetTransactions;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RunPatientTransactionsParser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser:patient-transactions';

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
        $dateFrom = Carbon::now()
            ->setTimezone('utc')
            ->startOfDay()
            ->subDays(2);
        $dateTo = Carbon::now()
            ->setTimezone('utc')
            ->startOfDay()
            ->addDays(1);

        $job = (new GetLocations())->onQueue('payments');
        dispatch($job);
        $job = (new GetTransactions($dateFrom, $dateTo))->onQueue('payments');
        dispatch($job);
        
        //@todo [OA parsers] uncomment when resolve problems with OA
//        $job = (new PaymentsParser($dateFrom, $dateTo))->onQueue('payments');
//        dispatch($job);
        $job = (new CalculatePatientBalance())->onQueue('payments');
        dispatch($job);
        \Artisan::call('square:get-customers-data');
    }
}

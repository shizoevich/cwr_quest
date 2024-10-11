<?php

namespace App\Console\Commands\ExcelReport;

use App\Services\ExcelReport\PaymentAvailabilityExcelReport;
use Illuminate\Console\Command;

class GeneratePaymentAvailabilityExcelReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */ 
    protected $signature = 'generate:payment-availability-report'; 

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
        $paymentAvailability = new PaymentAvailabilityExcelReport(); 
        $paymentAvailability->generateReport();
    }
}

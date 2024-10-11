<?php

namespace App\Console\Commands\ExcelReport;

use App\Services\ExcelReport\LucetPatientVisitsExcelReport;
use Illuminate\Console\Command;

class GenerateLucetPatientVisitsExcelReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:lucet-patient-visits-report {subscriber_id}'; 

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
        $excelProgressNoteReport = new LucetPatientVisitsExcelReport($this->argument('subscriber_id'));
        $excelProgressNoteReport->generateReport();
    }
}

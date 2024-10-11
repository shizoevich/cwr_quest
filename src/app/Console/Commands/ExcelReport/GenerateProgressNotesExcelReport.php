<?php

namespace App\Console\Commands\ExcelReport;

use App\Services\ExcelReport\ProgressNotesExcelReport;
use Illuminate\Console\Command;

class GenerateProgressNotesExcelReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:progress-notes-report {start_date=2022-10-31} {end_date=2023-10-31}'; 

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
        $excelProgressNoteReport = new ProgressNotesExcelReport($this->argument('start_date'), $this->argument('end_date'));
        $excelProgressNoteReport->generateReport();
    }
}

<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class AssignVisitsToAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'visits:assign-to-appointments {--start-date=} {--end-date=}';

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
        if($this->option('start-date')) {
            $startDate = Carbon::parse($this->option('start-date'));
        }
        if($this->option('end-date')) {
            $endDate = Carbon::parse($this->option('end-date'));
        }
        \Bus::dispatchNow(new \App\Jobs\Salary\AssignVisitsToAppointments($startDate, $endDate));
    }
}

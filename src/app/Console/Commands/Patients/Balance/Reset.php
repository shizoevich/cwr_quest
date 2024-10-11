<?php

namespace App\Console\Commands\Patients\Balance;

use App\Jobs\Patients\ResetBalance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class Reset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patients:reset-balance {--end-date=} {--patient-id=}';

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
        $endDate = $this->option('end-date');
        if(is_null($endDate)) {
            $this->error('The end-date option is required');
            return false;
        }
        $endDate = Carbon::parse($endDate);

        \Bus::dispatchNow(new ResetBalance($endDate, $this->option('patient-id')));
    }
}

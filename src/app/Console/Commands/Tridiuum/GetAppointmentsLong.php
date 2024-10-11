<?php

namespace App\Console\Commands\Tridiuum;

use Illuminate\Console\Command;
use App\Jobs\Tridiuum\GetProviders;
use App\Jobs\Tridiuum\GetAppointments as GetAppointmentsJob;
use Carbon\Carbon;

class GetAppointmentsLong extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tridiuum:get-appointments-long {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse appointment from Tridiuum';

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
        $job = (new GetProviders())->onQueue('tridiuum-long');
        dispatch($job);

        $from = Carbon::now()->subDays(20)->startOfDay();
        $to = Carbon::yesterday()->addMonth()->endOfDay();
        do {
            $job = (new GetAppointmentsJob(
                $this->argument('user_id'),
                $from->copy(),
                $from->addWeek(),
            ))->onQueue('tridiuum-long');
            dispatch($job);
        } while ($from->lte($to));
    }
}

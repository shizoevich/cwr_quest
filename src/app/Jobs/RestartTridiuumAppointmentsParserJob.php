<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;

class RestartTridiuumAppointmentsParserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->queue = 'restart-parser';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Redis::connection()->del('queues:tridiuum');
        Redis::connection()->del('queues:tridiuum-long');

        exec('sudo -i pm2 restart tridiuum');
        exec('sudo -i pm2 restart tridiuum-long');

        Artisan::call('tridiuum:get-appointments-long');
    }
}
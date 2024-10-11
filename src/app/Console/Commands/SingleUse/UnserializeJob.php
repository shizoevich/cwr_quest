<?php

namespace App\Console\Commands\SingleUse;

use Illuminate\Console\Command;

class UnserializeJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:unserialize {jobId}';

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
        $job = \DB::table('jobs')
            ->where('id', $this->argument('jobId'))
            ->first();

        if (!$job) {
            return;
        }

        $data = json_decode($job->payload, true);
        $command = unserialize($data['data']['command']);

        dump($command);
    }
}

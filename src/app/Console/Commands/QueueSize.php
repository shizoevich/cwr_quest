<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class QueueSize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:size';
    
    private $queues = [
        'default',
        'daily-parser',
        'officeally',
        'officeally-billing',
        'parser',
        'payments',
        'single-parser',
        'tridiuum',
        'tridiuum-availability',
        'tridiuum-parser',
        'visits-parser',
        'ringout',
        'ringout-socket',
        'workers-default',
        'workers-command',
    ];

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
        $data = [];
        foreach ($this->queues as $queue) {
            $data[] = [$queue, \Queue::size($queue)];
        }
        $this->output->table(['Queue Name', 'Queue Size'], $data);
    }
}

<?php

namespace App\Console\Commands\Tridiuum;

use App\Helpers\TridiuumHelper;
use Illuminate\Console\Command;

class AttachTridiuumDocs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tridiuum:update-attach-docs {appointmentId?}'; 

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /** @var TridiuumHelper */
    protected $tridiuumHelper;
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
        \Bus::dispatchNow(new \App\Jobs\Tridiuum\AttachTridiuumDocsJob($this->argument('appointmentId'))); 
    }
}

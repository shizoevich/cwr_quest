<?php

namespace App\Console\Commands\SingleUse;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Helpers\TridiuumHelper;
class TridiuumTrackCheck extends Command
{
    /**
     * The name and signature of the console command.
     * 
     * @var string
     */
    protected $signature = 'track:check {patientId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check unique id of Episode of care';

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
        $patientId = $this->argument('patientId');

        $tridiuumHelper = new TridiuumHelper();
        $tridiuumTrack = $tridiuumHelper->getCurrentTrack($patientId);
    
        Log::info($tridiuumTrack);
    }
}

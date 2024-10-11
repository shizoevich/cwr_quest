<?php

namespace App\Console\Commands\SingleUse;

use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Option;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckAlertPost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:alert-post  {patientExterlnalId} {alertMessage}'; 

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
        $patientExterlnalId = $this->argument('patientExterlnalId');
        
        $alertMessage  = $this->argument('alertMessage');
     
       $officeAllyHelper = new OfficeAllyHelper(Option::OA_ACCOUNT_1);

       $response = $officeAllyHelper->postPatientAlerts($patientExterlnalId, $alertMessage); 

       Log::info($response);
    }
}

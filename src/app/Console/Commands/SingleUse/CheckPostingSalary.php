<?php

namespace App\Console\Commands\SingleUse;

use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Models\Officeally\OfficeallyTransaction;
use App\Option;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckPostingSalary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:salary-posting {external_id}';

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
         $officeAllyHelper = new OfficeAllyHelper(Option::OA_ACCOUNT_1);
         $external_id = $this->argument('external_id');
         $payment = OfficeallyTransaction::where('external_id',$external_id )->first();
         $officeAllyHelper->makePostingNewApporoach($payment);
    }
}

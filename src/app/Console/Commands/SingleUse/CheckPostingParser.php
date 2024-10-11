<?php

namespace App\Console\Commands\SingleUse;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Option;

class CheckPostingParser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:posting'; 

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
        $officeAllyHelper = new OfficeAllyHelper(Option::OA_ACCOUNT_3);

        $isCreated = $officeAllyHelper->makePostingTestVariant();
        $arrayPosting = [];
        $arrayPosting[] = $isCreated ;

        Log::info($arrayPosting);
    }
}

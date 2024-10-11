<?php

namespace App\Console\Commands;

use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;
use App\Option;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckPasswordExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'password-expiration:check';

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
        //$this->checkTridiuum();
        $this->checkOfficeAlly();
    }
    
    //private function checkTridiuum()
    //{
        //$this->check(30, 'tridiuum_credentials');
    //}
    
    private function checkOfficeAlly()
    {
        $this->check(90, 'officeally_credentials');
    }
    
    private function check(int $liveDays, string $optionName)
    {
        $option = json_decode(Option::getOptionValue($optionName), true);
        if($option) {
            foreach ($option as $account => $credentials) {
                $updatedAt = data_get($credentials, 'updated_at');
                if(!$updatedAt) {
                    $this->notifyIfFailed("[{$optionName}] incorrect updated_at property for {$account} account.", $optionName);
                    continue;
                }
                $updatedAt = Carbon::parse($updatedAt)->startOfDay();
                if($updatedAt->copy()->addDays($liveDays - 5)->lte(Carbon::today())) {
                    $this->notifyIfFailed("[{$optionName}] Please Change password for account {$account} before {$updatedAt->copy()->addDays($liveDays)->toDateString()}", $optionName);
                }       
            }
        }
    }

    private function notifyIfFailed(string $message, string $optionName)
    {
        if ($optionName === 'officeally_credentials') {
            with(new ExceptionNotificator())->officeAllyNotifyAndSendToSentry(new AnErrorOccurred($message));
        } else if ($optionName === 'tridiuum_credentials') {
            with(new ExceptionNotificator())->tridiuumNotifyAndSendToSentry(new AnErrorOccurred($message));
        } else {
            with(new ExceptionNotificator())->notifyAndSendToSentry(new AnErrorOccurred($message));
        }
    }
}

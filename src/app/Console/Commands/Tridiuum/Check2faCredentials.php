<?php

namespace App\Console\Commands\Tridiuum;

use App\Helpers\Google\GmailInboxService;
use App\Option;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Google_Client;
use Google_Service_Gmail;
class Check2faCredentials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:2fa';

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
        $userId = "me";
        $gmail = new \Google_Service_Gmail((new GmailInboxService())->getClient());
        $messagesResponse = $gmail->users_messages->listUsersMessages($userId, ['maxResults' => 20]);
        $messages = $messagesResponse->getMessages();
        Log::info($messages);
    }
}

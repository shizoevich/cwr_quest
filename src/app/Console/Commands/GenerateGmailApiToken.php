<?php

namespace App\Console\Commands;

use App\Option;
use Illuminate\Console\Command;
use Google_Client;
use Google_Service_Gmail;

class GenerateGmailApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate gmail api token and push to DB - table options';

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
        $client = new Google_Client();
        $client->setApplicationName(config('app.name'));
        $client->setScopes(Google_Service_Gmail::MAIL_GOOGLE_COM);
        $client->setAuthConfig(json_decode(Option::getOptionValue('gmail_api_credentials'), true));
        $client->setPrompt('select_account consent');

        //redirect to auth url to get gmail permission
        $authUrl = $client->createAuthUrl();
        printf("Open the following link in your browser:\n%s\n", $authUrl);
    }
}

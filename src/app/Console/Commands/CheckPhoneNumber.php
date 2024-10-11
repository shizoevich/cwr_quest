<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Twilio\Rest\Lookups;
use Twilio\Rest\Client;

class CheckPhoneNumber extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'phone:check {phone}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set value in options table';

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
        $phone = sanitize_phone($this->argument('phone'));
        $client = new Lookups(new Client(
            config('twilio.twilio.connections.twilio.sid'),
            config('twilio.twilio.connections.twilio.token')
        ));
        $lookup = $client->phoneNumbers
            ->getContext($phone)
            ->fetch(['CountryCode' => 'US', 'Type' => 'carrier']);

        dump($lookup);
    }
}

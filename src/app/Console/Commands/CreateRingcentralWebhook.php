<?php

namespace App\Console\Commands;

use App\Services\Ringcentral\RingcentralWebhook;
use Illuminate\Console\Command;

class CreateRingcentralWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ringcentral:create-webhooks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create ringcentral webhooks';

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
        $ringcentralWebhook = new RingcentralWebhook();
        $ringcentralWebhook->store([
            '/restapi/v1.0/account/~/extension/~/message-store',
            '/restapi/v1.0/account/~/extension/~/presence?detailedTelephonyState=true&sipData=true',
        ]);
    }
}

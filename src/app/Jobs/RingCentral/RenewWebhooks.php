<?php

namespace App\Jobs\RingCentral;

use App\Services\Ringcentral\RingcentralWebhook;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RenewWebhooks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ringcentralWebhook = new RingcentralWebhook();
        $webhooks = $ringcentralWebhook->list();

        if (!count($webhooks)) {
            \Artisan::call('ringcentral:create-webhooks');
            return;
        }
        
        foreach ($webhooks as $webhook) {
            $minSeconds = 60 * 60 * 24 * 2; //seconds per 2 days
            if ($webhook['expiresIn'] <= $minSeconds) {
                $ringcentralWebhook->renew($webhook['id']);
            }
        }
    }
}

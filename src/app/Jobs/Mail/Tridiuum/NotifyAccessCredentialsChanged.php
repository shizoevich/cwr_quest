<?php

namespace App\Jobs\Mail\Tridiuum;

use App\Mail\Tridiuum\AccessCredentialsChanged;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class NotifyAccessCredentialsChanged implements ShouldQueue
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
        $emails = config('parser.emails');
        $emails = !empty($emails) ? explode(',', $emails) : [];
        if(empty($emails)) {
            return;
        }
        \Mail::to($emails)->send(new AccessCredentialsChanged());
    }
}

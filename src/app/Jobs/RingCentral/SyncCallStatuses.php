<?php

namespace App\Jobs\RingCentral;

use App\CallLog;
use App\Services\Ringcentral\RingcentralRingOut;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncCallStatuses implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ringOut = new RingcentralRingOut();
        
        CallLog::query()
            ->where('status_text', CallLog::INIT_STATUS)
            ->where('created_at', '>', \Carbon\Carbon::now()->subHour()->toDateTimeString())
            ->each(function (CallLog $callLog) use ($ringOut) {
                try {
                    $response = $ringOut->get($callLog->ring_central_call_id);
                    $callLog->update([
                        'status_text' => data_get($response, 'status.callStatus')
                    ]);
                } catch (\Throwable $e) {
                    \App\Helpers\SentryLogger::captureException($e);
//                    \Log::info($e);
                }
            });
    }
}

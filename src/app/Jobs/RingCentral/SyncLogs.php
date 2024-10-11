<?php

namespace App\Jobs\RingCentral;

use App\CallLog;
use App\Services\Ringcentral\RingcentralCallLog;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncLogs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return bool|null
     */
    public function handle()
    {
        $ringcentral = new RingcentralCallLog();
        $this->syncActiveLogs($ringcentral);
        $this->syncLogs($ringcentral);
    }
    
    private function storeLogs(array $records)
    {
        if (count($records) > 0) {
            $callLogs = CallLog::query()->whereNotNull('ring_central_session_id')->whereIn('ring_central_session_id',
                array_pluck($records, 'sessionId'))->get();
        
            $callLogs->each(function (CallLog $callLog) use ($records) {
                $record = array_first($records, function ($record) use ($callLog) {
                    return data_get($record, 'sessionId') == $callLog->ring_central_session_id;
                });
            
                if ($record) {
                    $callLog->update([
                        'duration'           => data_get($record, 'duration'),
                        'result'             => data_get($record, 'result'),
                        'reason'             => data_get($record, 'reason'),
                        'reason_description' => data_get($record, 'reasonDescription'),
                    ]);
                }
            });
        }
    }
    
    private function syncActiveLogs(RingcentralCallLog $ringcentral)
    {
        $records = $ringcentral->activeList();
        $this->storeLogs($records);
    }
    
    private function syncLogs(RingcentralCallLog $ringcentral)
    {
        $records = $ringcentral->list();
        $this->storeLogs($records);
    }
}

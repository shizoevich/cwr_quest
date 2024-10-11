<?php

namespace App\Jobs\RingCentral;

use App\Enums\Ringcentral\RingcentralCallerStatus;
use App\Enums\Ringcentral\RingcentralCallStatus;
use App\Events\Ringcentral\RingcentralCallChanged;
use App\Models\RingcentralCallLog;
use App\Services\Ringcentral\RingcentralRingOut;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncCallerStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * @var RingcentralCallLog
     */
    private $callLog;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(RingcentralCallLog $callLog)
    {
        $this->onQueue('ringout');
        $this->callLog = $callLog;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ringcentralRingOut = new RingcentralRingOut();
        try {
            $statuses = $ringcentralRingOut->get($this->callLog->ring_central_session_id);
            $callStatus = data_get(RingcentralCallStatus::MAPPED_STATUSES, $statuses['status']['callStatus']);
            $callerStatus = data_get(RingcentralCallerStatus::MAPPED_STATUSES, $statuses['status']['callerStatus']);
            $calleeStatus = data_get(RingcentralCallerStatus::MAPPED_STATUSES, $statuses['status']['calleeStatus']);
        } catch(\RingCentral\SDK\Http\ApiException $e) {
            if($e->getCode() === 404) {
                $callStatus = RingcentralCallStatus::STATUS_SUCCESS;
                $callerStatus = RingcentralCallerStatus::STATUS_FINISHED;
                $calleeStatus = RingcentralCallerStatus::STATUS_FINISHED;
            } else {
                throw $e;
            }
        }
        
        $updateData = [];
        if(!empty($callStatus)) {
            $updateData['call_status'] = $callStatus;
        }
        if(!empty($callerStatus)) {
            $updateData['caller_status'] = $callerStatus;
        }
        if(!empty($calleeStatus)) {
            $updateData['callee_status'] = $calleeStatus;
        }
        if(!empty($updateData)) {
            $this->callLog->update($updateData);
        }
        event(new RingcentralCallChanged($this->callLog));
    }
}

<?php

namespace App\Console\Commands\SingleUse;

use App\CallLog;
use App\Enums\Ringcentral\RingcentralCallerStatus;
use App\Enums\Ringcentral\RingcentralCallStatus;
use App\Enums\Ringcentral\RingcentralTelephonyStatus;
use App\Models\RingcentralCallLog;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SyncRingcentralCallLogs extends Command
{
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ringcentral:call-logs:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        CallLog::query()
            ->select('call_logs.*')
            ->leftJoin('ringcentral_call_logs', 'ringcentral_call_logs.ring_central_session_id', 'call_logs.ring_central_session_id')
            ->whereNull('ringcentral_call_logs.id')
            ->chunkById(5000, function(Collection $collection) {
                $data = [];
                $collection->each(function(CallLog $callLog) use (&$data) {
                    $data[] = [
                        'user_id' => $callLog->user_id,
                        'patient_id' => $callLog->patient_id,
                        'appointment_id' => $callLog->appointment_id,
                        'appointment_type' => 'tridiuum_appointment',
                        'ring_central_session_id' => $callLog->ring_central_call_id,
                        'phone_from' => $callLog->phone_from,
                        'phone_to' => $callLog->phone_to,
                        'telephony_status' => RingcentralTelephonyStatus::STATUS_NO_CALL,
                        'call_status' => RingcentralCallStatus::getStatusByName($callLog->status_text) ?? RingcentralCallStatus::STATUS_CANNOT_REACH,
                        'caller_status' => RingcentralCallerStatus::STATUS_FINISHED,
                        'callee_status' => RingcentralCallerStatus::STATUS_FINISHED,
                        'comment' => $callLog->comment,
                        'created_at' => $callLog->created_at,
                        'updated_at' => $callLog->updated_at,
                    ];
                });
                RingcentralCallLog::insert($data);
            }, 'call_logs.id');
            
    }
}

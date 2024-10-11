<?php

namespace App\Http\Controllers\Webhooks;

use App\Enums\Ringcentral\RingcentralTelephonyStatus;
use App\Events\Ringcentral\RingcentralCallChanged;
use App\Helpers\Constant\FaxConst;
use App\Models\RingcentralCallLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\RingCentral\SyncCallFaxes;
use App\Jobs\RingCentral\SyncCallerStatus;

class RingcentralWebhookController extends Controller
{
    public function index(Request $request)
    {
        $v = isset($_SERVER['HTTP_VALIDATION_TOKEN']) ? $_SERVER['HTTP_VALIDATION_TOKEN'] : '';
    
        if (strlen($v) > 0) {
            header("Validation-Token: {$v}");
        } else {
            if ($request->header('verification-token') !== config('ringcentral.webhook_verification_token')) {
                abort(403);
            }
            //fetch data from web hook - '/restapi/v1.0/account/~/extension/~/presence?detailedTelephonyState=true&sipData=true'
            if (preg_match("/\/restapi\/v1.0\/account\/[^\/]*\/extension\/[^\/]*\/presence/", $request->input('event'))) {
                return $this->handlePresence($request->all());
            }
            //fetch data from web hook -'/restapi/v1.0/account/~/extension/~/message-store'
            if (preg_match("/\/restapi\/v1.0\/account\/[^\/]*\/extension\/[^\/]*\/message-store/", $request->input('event'))) {
                return $this->handleMessageStoreFax($request->all());
            }
        }
        
        return null;
    }
    
    private function handlePresence(array $data)
    {
        $activeCalls = __data_get($data, 'body.activeCalls', []);
        foreach ($activeCalls as $call) {
            $sessionId = $call['telephonySessionId'];
            $callLog = RingcentralCallLog::query()->where('ring_central_session_id', $sessionId)->first();
            if (!$callLog) {
                continue;
            }
            $telephonyStatus = RingcentralTelephonyStatus::MAPPED_STATUSES[$call['telephonyStatus']];
            $updateData = [
                'telephony_status' => $telephonyStatus,
                'phone_from' => $call['from'],
                'phone_to' => $call['to'],
            ];
            if ($telephonyStatus === RingcentralTelephonyStatus::STATUS_NO_CALL) {
                $updateData['call_ends_at'] = Carbon::now();    //Carbon::parse($data['timestamp']);
            }
            $callLog->update($updateData);
            event(new RingcentralCallChanged($callLog));
            
            dispatch(new SyncCallerStatus($callLog));
        }
        
        return null;
    }

    private function handleMessageStoreFax(array $data)
    {
        $changes = __data_get($data, 'body.changes', []);
        foreach ($changes as $change) {
            if ($change['type'] === FaxConst::FAX_TYPE) {
                dispatch(new SyncCallFaxes(1));
                return;
            }
        }
    }
}

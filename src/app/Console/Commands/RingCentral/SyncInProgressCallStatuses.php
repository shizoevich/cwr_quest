<?php

namespace App\Console\Commands\RingCentral;

use Illuminate\Console\Command;
use App\Models\RingcentralCallLog;
use App\Services\Ringcentral\RingcentralCallLog as RingcentralCallLogService;
use App\Enums\Ringcentral\RingcentralCallStatus;
use App\Enums\Ringcentral\RingcentralCallerStatus;
use Carbon\Carbon;

class SyncInProgressCallStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ringcentral:sync-in-progress-call-statuses {--start-date=} {--end-date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $startDate = null;
        $endDate = null;
        if ($this->option('start-date') && $this->option('end-date')) {
            $startDate = Carbon::parse($this->option('start-date'))->startOfDay();
            $endDate = Carbon::parse($this->option('end-date'))->endOfDay();
        } else if ($this->option('start-date') || $this->option('end-date')) {
            $temp = $this->option('start-date') ?? $this->option('end-date');
            $startDate = Carbon::parse($temp)->startOfDay();
            $endDate = Carbon::parse($temp)->endOfDay();
        } else {
            $startDate = Carbon::today()->startOfDay();
            $endDate = Carbon::today()->endOfDay();
        }

        $ringcentral = new RingcentralCallLogService();
        $externalCallLogs = $ringcentral->list([
            'perPage' => 250,
            'type' => 'Voice',
            'direction' => 'Outbound',
            'dateFrom' => $startDate->toIso8601String(),
            'dateTo' => $endDate->toIso8601String()
        ]);

        if (!count($externalCallLogs)) {
            return;
        }

        $internalCallLogs = RingcentralCallLog::query()
            ->whereDate('created_at', '>=', $startDate->toDateString())
            ->whereDate('created_at', '<=', $endDate->toDateString())
            ->where('call_status', RingcentralCallStatus::STATUS_IN_PROGRESS)
            ->where('caller_status', RingcentralCallerStatus::STATUS_IN_PROGRESS)
            ->where('callee_status', RingcentralCallerStatus::STATUS_IN_PROGRESS)
            ->get();

        foreach ($externalCallLogs as $log) {
            $sessionId = $log['telephonySessionId'];
            $internalLog = $internalCallLogs->firstWhere('ring_central_session_id', $sessionId);
            if (!$internalLog) {
                continue;
            }

            $updateData = [];
            if (!$internalLog->phone_from) {
                $updateData['phone_from'] = __data_get($log, 'from.phoneNumber');
            }
            if (!$internalLog->phone_to) {
                $updateData['phone_to'] = __data_get($log, 'to.phoneNumber');
            }
            if (!$internalLog->call_ends_at) {
                $callStartsAt = Carbon::parse($internalLog->call_starts_at);
                $updateData['call_ends_at'] = $callStartsAt->addSeconds(__data_get($log, 'duration', 0));
            }

            switch (__data_get($log, 'result')) {
                case 'Call connected':
                    $updateData['call_status'] = RingcentralCallStatus::STATUS_SUCCESS;
                    $updateData['caller_status'] = RingcentralCallerStatus::STATUS_FINISHED;
                    $updateData['callee_status'] = RingcentralCallerStatus::STATUS_FINISHED;
                    break;
                case 'Busy':
                    $updateData['call_status'] = RingcentralCallStatus::STATUS_CANNOT_REACH;
                    $updateData['caller_status'] = RingcentralCallerStatus::STATUS_BUSY;
                    $updateData['callee_status'] = RingcentralCallerStatus::STATUS_BUSY;
            }
            
            if(!empty($updateData)) {
                $internalLog->update($updateData);
            }
        }
    }
}

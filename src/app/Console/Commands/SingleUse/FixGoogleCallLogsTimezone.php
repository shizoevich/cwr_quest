<?php

namespace App\Console\Commands\SingleUse;

use App\Models\Diagnose;
use App\Models\GoogleMeetingCallLog;
use App\Models\Patient\PatientDiagnose;
use App\PatientDiagnoseOld;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class FixGoogleCallLogsTimezone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-call-logs:fix-timezone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        GoogleMeetingCallLog::query()
            ->chunkById('1000', function(Collection $logs) {
                $logs->each(function(GoogleMeetingCallLog $log) {
                    $log->call_starts_at = optional($log->call_starts_at)->subHours(7);
                    $log->call_ends_at = optional($log->call_ends_at)->subHours(7);
                    $log->save();
                });
            });
    }
}

<?php

use Illuminate\Database\Seeder;
use App\Models\RingcentralCallLog;
use Illuminate\Database\Eloquent\Collection;

class UpdateRingcentralCallLogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RingcentralCallLog::chunkById('1000', function (Collection $logs) {
            $logs->each(function (RingcentralCallLog $log) {
                if (isset($log->appointment_id) && isset($log->appointment_type)) {
                    $log->update([
                        'call_subject_id' => $log->appointment_id,
                        'call_subject_type' => RingcentralCallLog::SUBJECT_TYPES[$log->appointment_type] ?? null,
                    ]);
                }
            });
        });
    }
}

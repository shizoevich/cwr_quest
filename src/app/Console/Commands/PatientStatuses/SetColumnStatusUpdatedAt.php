<?php

namespace App\Console\Commands\PatientStatuses;

use App\Patient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetColumnStatusUpdatedAt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set:column-status-updated-at {chunkSize}';

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
        $chunkSize = $this->argument('chunkSize');

        DB::connection('mysql_logger')
            ->table('hipaa_log_item')
            ->select([
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.patient_id')) as patient_id"),
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(dirty_fields, '$.updated_at.curr')) as status_updated_at"),
                DB::raw("MAX(appeared_at) as max_appeared_at"),
            ])
            ->where('event_name_id', Patient::getEventNamePatientUpdate())
            ->where('dirty_fields', 'like', '%status_id%')
            ->groupBy(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.patient_id'))"))
            ->orderBy('max_appeared_at', 'desc')
            ->chunk($chunkSize, function ($items) {
                foreach ($items as $item) {
                    $patient = Patient::select('id', 'status_updated_at')
                        ->where('patient_id', $item->patient_id)
                        ->first();
                    if ($patient) {
                        $patient->status_updated_at = $item->status_updated_at;
                        $patient->save();
                    }
                }
            });
    }
}

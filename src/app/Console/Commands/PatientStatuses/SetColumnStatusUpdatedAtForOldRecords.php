<?php

namespace App\Console\Commands\PatientStatuses;

use App\Patient;
use App\PatientStatus;
use App\PatientComment;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SetColumnStatusUpdatedAtForOldRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set:column-status-updated-at-old-records {chunkSize}';

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

        Patient::whereNull('status_updated_at')
            ->orderBy('created_at', 'desc')
            ->chunk($chunkSize, function ($patients) {
                foreach ($patients as $patient) {
                    if ($patient->status_id === PatientStatus::getNewId()) {
                        $patientCreatedAt = $patient->created_patient_date ?? $patient->created_at;
                        $patient->update(['status_updated_at' => Carbon::parse($patientCreatedAt)->toDateString()]);
                    } else {
                        $comment = PatientComment::select('created_at')
                            ->where('patient_id', $patient->id)
                            ->where('is_system_comment', 1)
                            ->whereNotNull('default_comment_id')
                            ->latest()
                            ->first();

                        if (isset($comment)) {
                            $statusCommentDate = $comment->created_at;
                            $patient->update(['status_updated_at' => $statusCommentDate]);
                        }
                    }
                }
            });
    }
}

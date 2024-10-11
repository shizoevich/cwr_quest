<?php

namespace App\Jobs;

use App\Helpers\NextcloudApi;
use App\PatientAssessmentForm;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DeleteTmpAssessmentForms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $davClient = new NextcloudApi();
        $date = Carbon::now()->subDay();
        $oldNotSavedForms = PatientAssessmentForm::where('updated_at', '<=', $date->toDateTimeString())
            ->where('status','=',PatientAssessmentForm::STATUS_TEMP)
            ->get();
        foreach ($oldNotSavedForms as $oldForm) {
            $r = $davClient->deleteFile($oldForm->file_nextcloud_path);
            $oldForm->delete();
        }
    }
}

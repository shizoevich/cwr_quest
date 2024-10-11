<?php

namespace App\Listeners;

use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Events\PatientLeadDocumentUpload;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\Database\WritePatientLeadDocumentUploadInfo as WritePatientLeadDocumentUploadInfoJob;

class WritePatientLeadDocumentUploadInfo
{
    use DispatchesJobs;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PatientLeadDocumentUpload  $event
     * @return void
     */
    public function handle(PatientLeadDocumentUpload $event)
    {
        $this->dispatchNow(new WritePatientLeadDocumentUploadInfoJob($event->document));
    }
}

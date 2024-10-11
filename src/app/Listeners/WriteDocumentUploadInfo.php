<?php

namespace App\Listeners;

use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Events\PatientDocumentUpload;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\Database\WriteDocumentUploadInfo as WriteDocumentUploadInfoJob;

class WriteDocumentUploadInfo
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
     * @param  PatientDocumentUpload  $event
     * @return void
     */
    public function handle(PatientDocumentUpload $event)
    {
        $this->dispatchNow(new WriteDocumentUploadInfoJob($event->document));
    }
}

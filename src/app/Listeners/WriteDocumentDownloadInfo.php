<?php

namespace App\Listeners;

use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Events\PatientDocumentDownloaded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\Database\WriteDocumentDownloadInfo as WriteDocumentDownloadInfoJob;

class WriteDocumentDownloadInfo
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
     * @param  PatientDocumentDownloaded  $event
     * @return void
     */
    public function handle(PatientDocumentDownloaded $event)
    {
        if(!$event->directDownload){
            $this->dispatchNow(new WriteDocumentDownloadInfoJob($event->sharedDocument));
        }
    }
}

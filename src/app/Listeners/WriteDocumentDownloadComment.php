<?php

namespace App\Listeners;

use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Events\PatientDocumentDownloaded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\Comments\WriteDocumentDownloadComment as WriteDocumentDownloadCommentJob;

class WriteDocumentDownloadComment
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
        $this->dispatchNow(new WriteDocumentDownloadCommentJob($event->sharedDocument, $event->directDownload));
    }
}

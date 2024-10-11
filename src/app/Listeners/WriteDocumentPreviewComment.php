<?php

namespace App\Listeners;

use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Events\PatientDocumentPreview;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\Comments\WriteDocumentPreviewComment as WriteDocumentPreviewCommentJob;

class WriteDocumentPreviewComment
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
     * @param  PatientDocumentPreview  $event
     * @return void
     */
    public function handle(PatientDocumentPreview $event)
    {

        $this->dispatchNow(new WriteDocumentPreviewCommentJob($event->sharedDocument, $event->directDownload, $event->uniqueId));
    }
}

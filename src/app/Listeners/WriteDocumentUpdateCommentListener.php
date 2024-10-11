<?php

namespace App\Listeners;

use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Events\PatientDocumentUpdate;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\Comments\WriteDocumentUpdateCommentInfo as WriteDocumentUpdateCommentInfoJob;

class WriteDocumentUpdateCommentListener
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
     * @param  PatientDocumentUpdate  $event
     * @return void
     */
    public function handle(PatientDocumentUpdate $event)
    {
        $this->dispatchNow(new WriteDocumentUpdateCommentInfoJob($event->sharedDocument, $event->directDownload, $event->uniqueId));
    }
}

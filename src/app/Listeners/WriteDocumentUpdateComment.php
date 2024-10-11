<?php

namespace App\Listeners;

use App\Jobs\Database\UpdateDocumentSendInfoDropped;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Events\PatientDocumentUpdateStatus;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\Comments\WriteDocumentUpdateComment as WriteDocumentUpdateCommentJob;

class WriteDocumentUpdateComment
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
     * @param  PatientDocumentUpdateStatus  $event
     * @return void
     */
    public function handle(PatientDocumentUpdateStatus $event)
    {
        $this->dispatchNow(new WriteDocumentUpdateCommentJob($event->updatedDocument, $event->directDownload));
    }
}

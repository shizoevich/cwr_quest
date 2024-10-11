<?php

namespace App\Listeners;

use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Events\PatientDocumentSent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\Comments\WriteDocumentSentComment as WriteDocumentSentCommentJob;

class WriteDocumentSentComment
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
     * @param  PatientDocumentSent  $event
     * @return void
     */
    public function handle(PatientDocumentSent $event)
    {
        $this->dispatchNow(new WriteDocumentSentCommentJob($event->sharedDocument, $event->directDownload));
    }
}

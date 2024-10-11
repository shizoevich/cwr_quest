<?php

namespace App\Listeners;

use App\Patient;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NeedsWriteSystemComment
{
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
     * @param \App\Events\NeedsWriteSystemComment|NeedsWriteSystemComment $event
     *
     * @return void
     */
    public function handle(\App\Events\NeedsWriteSystemComment $event)
    {
        $patient = Patient::find($event->getPatientId());
        $patient->comments()->create([
            'is_system_comment' => true,
            'comment' => $event->getComment(),
        ]);
    }
}

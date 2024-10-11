<?php

namespace App\Listeners;

use App\Events\PatientDocumentStatusChanged;
use App\PatientDocument;
use App\PatientDocumentComment;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;

class WriteDocumentStatusChangedComment
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
     * @param  PatientDocumentStatusChanged  $event
     * @return void
     */
    public function handle(PatientDocumentStatusChanged $event)
    {
        $user = Auth::user();
        $userMeta = $user->meta;
        $userName = $userMeta->firstname . ' ' . $userMeta->lastname;
        $status = 'Private';
        if(!$event->onlyForAdmin) {
            $status = 'Public';
        }
        PatientDocumentComment::create([
            'patient_documents_id' => $event->documentId,
            'document_model' => PatientDocument::class,
            'admin_id' => $user->id,
            'content' => trans('comments.document_status_changed_by', ['name' => $userName, 'status' => $status]),
            'is_system_comment' => true,
        ]);
    }
}

<?php

namespace App\Jobs\Comments;

use App\PatientDocumentShared;
use App\PatientDocumentComment;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class WriteDocumentUpdateComment extends WriteComment implements ShouldQueue
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $commentContent = __('comments.document_status_update', [
            'status' => $this->document->documentSharedLog->sharedStatus->status,
            'recipient' => $this->document->recipient,
        ]);

        $this->data['content'] = $commentContent;
        $this->data['created_at'] = $this->document->documentSharedLog->updated_at;
        $this->data['is_system_comment'] = true;

        PatientDocumentComment::create($this->data);
    }
}

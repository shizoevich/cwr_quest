<?php

namespace App\Jobs\Comments;

use App\PatientDocumentComment;
use App\PatientDocumentShared;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class WriteDocumentSentComment extends WriteComment implements ShouldQueue
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = \Auth::user();
        $sender = '';
        if($user->isAdmin() || $user->isSecretary()) {
            $meta = $user->meta;
            if(!is_null($meta)) {
                $sender = $meta->firstname . ' ' . $meta->lastname;
            }
        } else if(!is_null($user->provider)) {
            $sender = $user->provider->provider_name;
        }

        $commentContent = __('comments.document_sent_by', [
            'method' => $this->document->sharedMethod->method,
            'recipient' => $this->document->recipient,
            'sender' => $sender,
        ]);

        $this->data['content'] = $commentContent;
        $this->data['is_system_comment'] = true;

        PatientDocumentComment::create($this->data);
    }
}

<?php

namespace App\Jobs\Comments;

use App\PatientDocumentComment;
use App\PatientDocumentShared;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class WriteDocumentDownloadComment extends WriteComment implements ShouldQueue
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $recipient = $this->document->recipient;

        if($this->directDownload){
            $user = \Auth::user();

            if($user->provider === null){

                $recipient = implode(' ', array($user->meta->firstname, $user->meta->lastname));

            } else {
                $recipient = $user->provider->provider_name;
            }

        }
        $commentContent = __('comments.document_download_by', [
            'recipient' => $recipient,
        ]);

        $this->data['content'] = $commentContent;
        $this->data['is_system_comment'] = true;

        PatientDocumentComment::create($this->data);
    }
}

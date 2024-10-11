<?php

namespace App\Jobs\Database;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\PatientDocumentShared;
use App\SharedDocumentStatus;

class UpdateDocumentSendInfoOpened implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $document_id;
    private $timestamp;

    /**
     * UpdateDocumentSendInfoOpened constructor.
     *
     * @param $id
     * @param $ts
     */
    public function __construct($id, $ts)
    {
        $this->document_id = $id;
        $this->timestamp = $ts;
    }

    public function handle()
    {
        $sharedStatus = SharedDocumentStatus::where('status', '=','opened')->first();

        $document = PatientDocumentShared::where('external_id', '=', $this->document_id)->first();

        if($document) {
            $document->documentSharedLog->updated_at = $this->timestamp;
            $document->documentSharedLog->sharedStatus()->associate($sharedStatus)->save();
            return $document;
        } else {
            return null;
        }

    }
}

<?php

namespace App\Jobs\Database;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\PatientDocumentShared;
use App\SharedDocumentStatus;

class UpdateDocumentSendInfoDropped implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $document_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->document_id = $id;
    }

    public function handle()
    {
        $sharedStatus = SharedDocumentStatus::where('status', '=','dropped')->first();

        $document = PatientDocumentShared::where('external_id', '=', $this->document_id)
            ->whereHas('documentSharedLog', function ($query){
                $query->whereHas('sharedStatus', function ($query){
                    $query->where('status', '<>', 'dropped');
                });
            })
            ->with('documentSharedLog')
            ->first();

        if($document) {
            $document->documentSharedLog->sharedStatus()->associate($sharedStatus)->save();
            return $document;
        } else {
            return null;
        }

    }
}

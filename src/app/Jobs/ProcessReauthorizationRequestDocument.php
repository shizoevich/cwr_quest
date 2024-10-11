<?php

namespace App\Jobs;

use App\Models\SubmittedReauthorizationRequestForm;
use App\Models\SubmittedReauthorizationRequestFormStage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessReauthorizationRequestDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $document;

    public function __construct($document)
    {
        $this->document = $document;
    }

    public function handle()
    {
        $readyToSendId = SubmittedReauthorizationRequestFormStage::getReadyToSendId();

        $submittedBy = SubmittedReauthorizationRequestForm::getSubmittedBy($this->document);

        SubmittedReauthorizationRequestForm::create([
            'document_id' => $this->document->id,
            'document_type' => get_class($this->document),
            'patient_id' => $this->document->patient_id,
            'submitted_by' => $submittedBy,
            'stage_id' => $readyToSendId,
        ]);
    }
}

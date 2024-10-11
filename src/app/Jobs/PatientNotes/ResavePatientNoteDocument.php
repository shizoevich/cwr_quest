<?php

namespace App\Jobs\PatientNotes;

use App\Http\Controllers\Utils\PdfUtils;
use App\PatientNote;
use App\Traits\GoogleDrive\CopyPatientNoteAndAssessmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ResavePatientNoteDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, PdfUtils, CopyPatientNoteAndAssessmentService;

    private $note;

    public function __construct(PatientNote $note)
    {
        $this->note = $note;
    }

    public function handle()
    {
        $this->generatePdfNoteOnFly($this->note->toArray());

        if ($this->note->google_drive) {
            if (
                ($this->note->id !== null) &&
                ($this->note->patients_id !== null) &&
                ($this->note->date_of_service !== null)
            ) {
                $this->makeCopyPatientNoteAndAssessment(
                    $this->note->id,
                    '888',
                    $this->note->patients_id,
                    $this->note->date_of_service,
                    'progress_notes'
                );
            }
        }
    }
}

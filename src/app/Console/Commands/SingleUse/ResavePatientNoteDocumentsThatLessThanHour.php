<?php

namespace App\Console\Commands\SingleUse;

use Illuminate\Console\Command;
use App\PatientNote;
use App\Http\Controllers\Utils\PdfUtils;
use App\Jobs\PatientNotes\ResavePatientNoteDocument;
use App\Models\TreatmentModality;
use App\Traits\GoogleDrive\CopyPatientNoteAndAssessmentService;
use Carbon\Carbon;

class ResavePatientNoteDocumentsThatLessThanHour extends Command
{
    use PdfUtils, CopyPatientNoteAndAssessmentService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resave-documents:patient-notes-that-less-than-hour';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $newTreatmentModality = TreatmentModality::DEFAULT_IN_PERSON_TREATMENT_MODALITY;
        $newTreatmentModalityId = TreatmentModality::getTreatmentModalityIdByName($newTreatmentModality);

        PatientNote::query()
            ->whereIn('treatment_modality', ['Individual 30 min', 'Individual 45 min'])
            ->where('patient_notes.is_finalized', '=', true)
            ->whereNotNull('patient_notes.patients_id')
            ->orderBy('patient_notes.finalized_at', 'desc')
            ->chunkById(100, function ($notes) use ($newTreatmentModality, $newTreatmentModalityId) {
                foreach ($notes as $note) {
                    $strToDump = 'Patient note id: ' . $note->id . ';';
                    dump($strToDump);

                    $startDate = Carbon::parse($note->date_of_service . ' ' . $note->start_time);
                    // $startDate->addMinutes(rand(0, 3));
                    $newStartTime = $startDate->format('g:i A');
                    $newEndTime = $startDate->addMinutes(60)->format('g:i A');

                    $note->update([
                        'treatment_modality' => $newTreatmentModality,
                        'treatment_modality_id' => $newTreatmentModalityId,
                        'start_time' => $newStartTime,
                        'end_time' => $newEndTime
                    ]);

                    \Bus::dispatchNow(new ResavePatientNoteDocument($note));
                }
            }, 'patient_notes.id', 'id');
    }
}

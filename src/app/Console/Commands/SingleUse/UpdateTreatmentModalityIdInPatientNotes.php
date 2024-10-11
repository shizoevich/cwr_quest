<?php

namespace App\Console\Commands\SingleUse;

use Illuminate\Console\Command;
use App\PatientNote;
use App\Models\TreatmentModality;
use App\Traits\GoogleDrive\CopyPatientNoteAndAssessmentService;

class UpdateTreatmentModalityIdInPatientNotes extends Command
{
    use CopyPatientNoteAndAssessmentService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:treatment-modality-id-in-patient-notes';

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
        PatientNote::query()
            ->whereIn('treatment_modality', ['Individual 30 min', 'Individual 45 min', 'Individual 60 min'])
            ->whereNull('treatment_modality_id')
            ->chunk(1000, function ($notes) {
                foreach ($notes as $note) {
                    $treatmentModalityId = TreatmentModality::getTreatmentModalityIdByName($note->treatment_modality);

                    $note->update(['treatment_modality_id' => $treatmentModalityId]);
                }
            });
    }
}

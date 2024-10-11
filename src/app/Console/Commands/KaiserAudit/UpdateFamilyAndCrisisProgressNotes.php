<?php

namespace App\Console\Commands\KaiserAudit;

use App\Http\Controllers\Utils\PdfUtils;
use App\PatientNote;
use App\Traits\GoogleDrive\CopyPatientNoteAndAssessmentService;
use Illuminate\Console\Command;

class UpdateFamilyAndCrisisProgressNotes extends Command
{
    use PdfUtils, CopyPatientNoteAndAssessmentService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:update-family-and-crisis-progress-notes {filePath?}';

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
        foreach ($this->patientNotes() as $patientNote) {
            $patientNote->update([
                'treatment_modality' => 'Individual 60 min',
            ]);

            $this->generatePdfNoteOnFly($patientNote->toArray());

            if ($patientNote->google_drive) {
                if (
                    ($patientNote->id !== null) &&
                    ($patientNote->patients_id !== null) &&
                    ($patientNote->date_of_service !== null)
                ) {
                    $this->makeCopyPatientNoteAndAssessment(
                        $patientNote->id,
                        '888',
                        $patientNote->patients_id,
                        $patientNote->date_of_service,
                        'progress_notes',
                    );
                }
            }
        }
    }

    private function patientNotes(): \Generator
    {
        foreach ($this->parseFile() as $item) {
            $patientNote = PatientNote::select(['patient_notes.*'])
                ->join('appointments', 'appointments.id', 'patient_notes.appointment_id')
                ->where('appointments.patients_id', $item[4])
                ->where('appointments.appointment_statuses_id', 1)
                ->whereRaw("date(from_unixtime(appointments.time)) = '{$item[3]}'")
                ->where('patient_notes.is_finalized', '=', true)
                ->whereIn('patient_notes.treatment_modality', ['Family', 'Crisis'])
                ->first();

            if ($patientNote) {
                yield $patientNote;
            }
        }
    }

    private function parseFile(): \Generator
    {
        $filePath = $this->argument('filePath') ?? storage_path('app/temp/25_patients.csv');

        if (($open = fopen($filePath, "r")) !== false) {
            while (($data = fgetcsv($open, 1000, ";")) !== false) {
                yield $data;
            }

            fclose($open);
        }
    }
}

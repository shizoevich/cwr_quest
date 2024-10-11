<?php

namespace App\Console\Commands\KaiserAudit;

use App\Http\Controllers\Utils\PdfUtils;
use App\PatientNote;
use App\Traits\GoogleDrive\CopyPatientNoteAndAssessmentService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use InvalidArgumentException;

class ResaveProgressNotesThatLessThanHour extends Command
{
    use AppointmentAvailabilityTrait, PdfUtils, CopyPatientNoteAndAssessmentService;

    const MIN_SESSION_LENGTH = 53 * Carbon::SECONDS_PER_MINUTE;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:resave-patient-notes-that-less-than-hour {filePath?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update patient notes duration to >= 53m';

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
        config(['database.connections.mysql.timezone' => 'America/Los_Angeles']);
        \DB::reconnect('mysql');

        foreach ($this->patientNotes() as $patientNote) {
            try {
                $this->processPatientNote($patientNote);
            } catch (InvalidArgumentException $exception) {
                $this->warn($exception->getMessage());
            }
        }
    }

    private function processPatientNote(PatientNote $patientNote)
    {
        if ($this->getVisitDurationFromLogs($patientNote->appointment)) {
            $startAt = Carbon::parse($this->getVisitStartTimeFromLogs($patientNote->appointment));
            $endAt = Carbon::parse($this->getVisitEndTimeFromLogs($patientNote->appointment));

            if ($endAt->diffInSeconds($startAt) >= self::MIN_SESSION_LENGTH) {
                return $this->updatePatientNote($patientNote, $startAt, $endAt);
            }
        }

        [$appointmentStartsAt, $appointmentEndsAt] = $this->findAppointmentStartAndEndTime($patientNote->appointment);

        $pad = $appointmentEndsAt->diffInSeconds($appointmentStartsAt) - self::MIN_SESSION_LENGTH;
        $startAt = $appointmentStartsAt->copy()->addSeconds(random_int(min(Carbon::SECONDS_PER_MINUTE, $pad), $pad));
        $endAt = $startAt->copy()->addSeconds(random_int(self::MIN_SESSION_LENGTH, $appointmentEndsAt->diffInSeconds($startAt)));

        return $this->updatePatientNote($patientNote, $startAt, $endAt);
    }

    private function updatePatientNote(PatientNote $patientNote, Carbon $startAt, Carbon $endAt): PatientNote
    {
        $patientNote->update([
            'start_time' => $startAt->format('g:i A'),
            'end_time' => $endAt->format('g:i A'),
            'treatment_modality' => in_array($patientNote->treatment_modality, ['Individual 30 min', 'Individual 45 min'])
                ? 'Individual 60 min'
                : $patientNote->treatment_modality
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

        return $patientNote;
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
                ->with([
                    'appointment' => function ($query) {
                        return $query->select(['appointments.*'])
                            ->selectRaw('from_unixtime(time) as start_at');
                    },
                ])
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

    protected function minSessionLength(): int
    {
        return self::MIN_SESSION_LENGTH;
    }
}

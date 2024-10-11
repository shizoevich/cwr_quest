<?php

namespace App\Console\Commands;

use App\PatientNote;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteProgressNotesWithEqDOS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pn:del-duplicates';

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
        $delDate = Carbon::now();
        $patients = \DB::select(\DB::raw("
            SELECT patient_notes.patients_id, COUNT(*) AS c, MAX(updated_at) AS actual_pn_date, date_of_service
            FROM patient_notes
            WHERE date_of_service IS NOT NULL AND deleted_at IS NULL
            GROUP BY patients_id, date_of_service
            HAVING COUNT(id) > 1
        "));
        foreach ($patients as $patient) {
            PatientNote::query()
                ->where('patients_id', $patient->patients_id)
                ->where('date_of_service', $patient->date_of_service)
                ->where('updated_at', '!=', $patient->actual_pn_date)
                ->each(function ($patientNote) use ($delDate) {
                    $patientNote->update(['deleted_at' => $delDate]);
                });
        }
        $this->output->text('Del Date: ' . $delDate->toDateTimeString());
    }
}

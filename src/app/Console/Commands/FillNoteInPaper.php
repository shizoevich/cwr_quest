<?php

namespace App\Console\Commands;

use App\Patient;
use Illuminate\Console\Command;

class FillNoteInPaper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill:paper-note';

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
        $patients = Patient::whereHas('appointments')
            ->where('completed_appointment_count', '>', 0)
            ->get();

        $counter = 1;
        $all = count($patients);
        foreach($patients as $patient) {
            $patient->appointments()
                ->onlyVisitCreated()
                ->orderBy('time', 'asc')
                ->limit($patient->completed_appointment_count)
                ->each(function ($appointment) {
                    $appointment->update(['note_on_paper' => true]);
                });
            echo "Processed: $counter/$all\n";
            $counter++;
        }
    }
}

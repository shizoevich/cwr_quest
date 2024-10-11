<?php

namespace App\Console\Commands\SingleUse;

use App\Patient;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SanitizePatientPhone extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patient-phone:sanitize';

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
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
       Patient::query()
           ->whereNotNull('cell_phone')
           ->orWhereNotNull('home_phone')
           ->orWhereNotNull('work_phone')
           ->chunkById(1000, function(Collection $patients) {
               $patients->each(function(Patient $patient) {
                   $patient->home_phone = sanitize_phone($patient->home_phone);
                   $patient->work_phone = sanitize_phone($patient->work_phone);
                   $patient->cell_phone = sanitize_phone($patient->cell_phone);
                   $patient->save();
               });
           });
    }
}

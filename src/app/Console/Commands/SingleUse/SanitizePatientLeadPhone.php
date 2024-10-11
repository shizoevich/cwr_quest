<?php

namespace App\Console\Commands\SingleUse;

use App\Models\Patient\Lead\PatientLead;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SanitizePatientLeadPhone extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patient-lead-phone:sanitize';

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
       PatientLead::query()
           ->withTrashed()
           ->whereNotNull('cell_phone')
           ->orWhereNotNull('home_phone')
           ->orWhereNotNull('work_phone')
           ->chunkById(1000, function(Collection $patientLeads) {
               $patientLeads->each(function(PatientLead $patientLead) {
                   $patientLead->home_phone = sanitize_phone($patientLead->home_phone);
                   $patientLead->work_phone = sanitize_phone($patientLead->work_phone);
                   $patientLead->cell_phone = sanitize_phone($patientLead->cell_phone);
                   $patientLead->save();
               });
           });
    }
}

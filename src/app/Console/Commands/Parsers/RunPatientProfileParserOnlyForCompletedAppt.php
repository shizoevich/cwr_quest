<?php

namespace App\Console\Commands\Parsers;

use App\Appointment;
use App\Jobs\Parsers\Guzzle\PatientProfilesParser;
use App\Status;
use Illuminate\Console\Command;

class RunPatientProfileParserOnlyForCompletedAppt extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser:patient-profile-for-completed-appt';

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
        $patientIds = Appointment::query()
            ->select('patients.patient_id')
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->where('appointment_statuses_id', '=', Status::getCompletedId())
            ->groupBy('patients.patient_id')
            ->pluck('patients.patient_id')
            ->toArray();
        dispatch(with(new PatientProfilesParser($patientIds))->onQueue('parser'));
    }
}

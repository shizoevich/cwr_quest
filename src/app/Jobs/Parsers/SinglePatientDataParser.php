<?php

namespace App\Jobs\Parsers;

use App\Jobs\Parsers\Guzzle\PatientAlertsParser;
use App\Jobs\Parsers\Guzzle\PatientProfilesParser;
use App\Jobs\Parsers\Guzzle\PatientsParser;
use App\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SinglePatientDataParser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Patient */
    private $patient;

    /**
     * Create a new job instance.
     *
     * @param Patient $patient
     */
    public function __construct(Patient $patient)
    {
        $this->patient = $patient;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            \Bus::dispatchNow(new PatientsParser('single-parser', false));
            \Bus::dispatchNow(new PatientProfilesParser([$this->patient->patient_id]));
            \Bus::dispatchNow(new PatientAlertsParser([$this->patient->patient_id]));
        } finally {
            $this->patient->update([
                'start_synchronization_time' => null,
            ]);
        }
    }
}

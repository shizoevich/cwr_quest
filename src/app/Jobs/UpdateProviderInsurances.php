<?php

namespace App\Jobs;

use App\Patient;
use App\PatientInsurance;
use App\ProviderInsurance;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateProviderInsurances implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $patients = Patient::query()
            ->select([
                'patients_has_providers.providers_id',
                'patients_has_providers.patients_id',
                'patients.primary_insurance_id',
                'patients.primary_insurance',
                'patients.secondary_insurance',
                'patients.id',
            ])
            ->join('patients_has_providers', 'patients_has_providers.patients_id', '=', 'patients.id')
            ->where('patients_has_providers.chart_read_only', false)
            ->get();
        
        foreach($patients as $patient) {
            $insurances = [
                $patient->primary_insurance,
                $patient->secondary_insurance,
            ];
            $index = 0;
            foreach($insurances as $i) {
                if (!empty($i)) {
                    $patientInsurance = PatientInsurance::firstOrCreate(['insurance' => $i]);

                    if ($index === 0 && (empty($patient->primary_insurance_id) || $patient->primary_insurance_id != $patientInsurance->id)) {
                        $patient->primary_insurance_id = $patientInsurance->id;
                        $patient->save();
                    }

                    $providerInsurance = ProviderInsurance::where('provider_id', $patient->providers_id)
                        ->where('insurance_id', $patientInsurance->id)
                        ->first();
                    if (empty($providerInsurance)) {
                        ProviderInsurance::create([
                            'insurance_id' => $patientInsurance->id,
                            'provider_id' => $patient->providers_id,
                        ]);
                    }
                }
                $index++;
            }
        }
    }
}

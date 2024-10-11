<?php

use App\Appointment;
use App\Models\TreatmentModality;
use Illuminate\Database\Seeder;

class AppointmentTreatmentModalityIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Appointment::query()
            ->whereNull('treatment_modality_id')
            ->chunk(1000, function ($appointments) {
                foreach ($appointments as $appointment) {
                    $treatmentModalityName = null;

                    if ($appointment->is_initial) {
                        $treatmentModalityName = $appointment->reason_for_visit === Appointment::REASON_TELEHEALTH ?
                            TreatmentModality::INITIAL_EVALUATION_TELEHEALTH_TREATMENT_MODALITY :
                            TreatmentModality::INITIAL_EVALUATION_TREATMENT_MODALITY;
                    } else {
                        $treatmentModalityName = $appointment->reason_for_visit === Appointment::REASON_TELEHEALTH ?
                            TreatmentModality::DEFAULT_TELEHEALTH_TREATMENT_MODALITY :
                            TreatmentModality::DEFAULT_IN_PERSON_TREATMENT_MODALITY;
                    }

                    $treatmentModalityId = TreatmentModality::getTreatmentModalityIdByName($treatmentModalityName);
                    
                    $appointment->update(['treatment_modality_id' => $treatmentModalityId]);
                }
            });
    }
}

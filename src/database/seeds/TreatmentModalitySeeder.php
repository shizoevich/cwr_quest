<?php

use App\Models\TreatmentModality;
use App\PatientInsuranceProcedure;
use Illuminate\Database\Seeder;

class TreatmentModalitySeeder extends Seeder
{
    const INDIVIDUAL_30_MIN_PROCEDURE_CODE = 90832;
    const INDIVIDUAL_45_MIN_PROCEDURE_CODE = 90834;
    const INDIVIDUAL_60_MIN_PROCEDURE_CODE = 90837;
    const FAMILY_PSYCHOTHERAPY_WITHOUT_PATIENT_PRESENT_PROCEDURE_CODE = 90846;
    const FAMILY_PSYCHOTHERAPY_WITH_PATIENT_PRESENT_PROCEDURE_CODE = 90847;
    const INITIAL_EVALUATION_PROCEDURE_CODE = 90791;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $treatmentModalities = [
            [
                'insurance_procedure_code' => self::INITIAL_EVALUATION_PROCEDURE_CODE,
                'name' => 'Initial Evaluation',
                'order' => 1,
                'is_telehealth' => false,
                'duration' => 60,
                'min_duration' => 52,
                'max_duration' => null
            ],
            [
                'insurance_procedure_code' => self::INITIAL_EVALUATION_PROCEDURE_CODE,
                'name' => 'Initial Evaluation (Telehealth)',
                'order' => 1,
                'is_telehealth' => true,
                'duration' => 60,
                'min_duration' => 52,
                'max_duration' => null
            ],
            [
                'insurance_procedure_code' => self::INDIVIDUAL_30_MIN_PROCEDURE_CODE,
                'name' => 'Individual 30 min',
                'order' => 2,
                'is_telehealth' => false,
                'duration' => 30,
                'min_duration' => 16,
                'max_duration' => 37
            ],
            [
                'insurance_procedure_code' => self::INDIVIDUAL_30_MIN_PROCEDURE_CODE,
                'name' => 'Individual 30 min (Telehealth)',
                'order' => 2,
                'is_telehealth' => true,
                'duration' => 30,
                'min_duration' => 16,
                'max_duration' => 37
            ],
            [
                'insurance_procedure_code' => self::INDIVIDUAL_45_MIN_PROCEDURE_CODE,
                'name' => 'Individual 45 min',
                'order' => 3,
                'is_telehealth' => false,
                'duration' => 45,
                'min_duration' => 38,
                'max_duration' => 52
            ],
            [
                'insurance_procedure_code' => self::INDIVIDUAL_45_MIN_PROCEDURE_CODE,
                'name' => 'Individual 45 min (Telehealth)',
                'order' => 3,
                'is_telehealth' => true,
                'duration' => 45,
                'min_duration' => 38,
                'max_duration' => 52
            ],
            [
                'insurance_procedure_code' => self::INDIVIDUAL_60_MIN_PROCEDURE_CODE,
                'name' => 'Individual 60 min',
                'order' => 4,
                'is_telehealth' => false,
                'duration' => 60,
                'min_duration' => 52,
                'max_duration' => null
            ],
            [
                'insurance_procedure_code' => self::INDIVIDUAL_60_MIN_PROCEDURE_CODE,
                'name' => 'Individual 60 min (Telehealth)',
                'order' => 4,
                'is_telehealth' => true,
                'duration' => 60,
                'min_duration' => 52,
                'max_duration' => null
            ],
            [
                'insurance_procedure_code' => self::FAMILY_PSYCHOTHERAPY_WITHOUT_PATIENT_PRESENT_PROCEDURE_CODE,
                'name' => 'Family without patient present',
                'order' => 5,
                'is_telehealth' => false,
                'duration' => 45,
                'min_duration' => 26,
                'max_duration' => 50
            ],
            [
                'insurance_procedure_code' => self::FAMILY_PSYCHOTHERAPY_WITHOUT_PATIENT_PRESENT_PROCEDURE_CODE,
                'name' => 'Family without patient present (Telehealth)',
                'order' => 5,
                'is_telehealth' => true,
                'duration' => 45,
                'min_duration' => 26,
                'max_duration' => 50
            ],
            [
                'insurance_procedure_code' => self::INDIVIDUAL_30_MIN_PROCEDURE_CODE,
                'name' => 'Individual with family member 30 min',
                'order' => 6,
                'is_telehealth' => false,
                'duration' => 30,
                'min_duration' => 16,
                'max_duration' => 37
            ],
            [
                'insurance_procedure_code' => self::INDIVIDUAL_30_MIN_PROCEDURE_CODE,
                'name' => 'Individual with family member 30 min (Telehealth)',
                'order' => 6,
                'is_telehealth' => true,
                'duration' => 30,
                'min_duration' => 16,
                'max_duration' => 37
            ],
            [
                'insurance_procedure_code' => self::INDIVIDUAL_45_MIN_PROCEDURE_CODE,
                'name' => 'Individual with family member 45 min',
                'order' => 7,
                'is_telehealth' => false,
                'duration' => 45,
                'min_duration' => 38,
                'max_duration' => 52
            ],
            [
                'insurance_procedure_code' => self::INDIVIDUAL_45_MIN_PROCEDURE_CODE,
                'name' => 'Individual with family member 45 min (Telehealth)',
                'order' => 7,
                'is_telehealth' => true,
                'duration' => 45,
                'min_duration' => 38,
                'max_duration' => 52
            ],
            [
                'insurance_procedure_code' => self::INDIVIDUAL_60_MIN_PROCEDURE_CODE,
                'name' => 'Individual with family member 60 min',
                'order' => 8,
                'is_telehealth' => false,
                'duration' => 60,
                'min_duration' => 52,
                'max_duration' => null
            ],
            [
                'insurance_procedure_code' => self::INDIVIDUAL_60_MIN_PROCEDURE_CODE,
                'name' => 'Individual with family member 60 min (Telehealth)',
                'order' => 8,
                'is_telehealth' => true,
                'duration' => 60,
                'min_duration' => 52,
                'max_duration' => null
            ],
        ];

        foreach ($treatmentModalities as $treatmentModality) {
            $insuranceProcedureId = PatientInsuranceProcedure::where('code', $treatmentModality['insurance_procedure_code'])->first();

            TreatmentModality::updateOrCreate(
                ['name' => $treatmentModality['name']],
                [
                    'insurance_procedure_id' => $insuranceProcedureId->id,
                    'name' => $treatmentModality['name'],
                    'order' => $treatmentModality['order'],
                    'is_telehealth' => $treatmentModality['is_telehealth'],
                    'duration' => $treatmentModality['duration'],
                    'min_duration' => $treatmentModality['min_duration'],
                    'max_duration' => $treatmentModality['max_duration'],
                ]
            );
        }
    }
}

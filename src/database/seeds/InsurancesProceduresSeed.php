<?php

use App\PatientInsuranceProcedure;
use Illuminate\Database\Seeder;

class InsurancesProceduresSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $procedures = [
            [
                'code' => '90791',
                'name' => 'Psychiatric Diagnostic Evaluation without Medical Services'
            ],
            [
                'code' => '90834',
                'name' => 'Psychotherapy Patient and/or Family 45 minutes'
            ],
            [
                'code' => '90837',
                'name' => 'Psychotherapy Patient and/or Family 60 minutes'
            ],
            [
                'code' => '90839',
                'name' => 'Psychotherapy For Crisis,initial 60 min'
            ],
            [
                'code' => '90840',
                'name' => 'Psychotherapy For Crisis add on 30 min'
            ],
            [
                'code' => '90846',
                'name' => 'Family Psychotherapy Family without patient'
            ],
            [
                'code' => '90847',
                'name' => 'Family Psychotherapy Family with patient present'
            ],
            [
                'code' => '90853',
                'name' => 'Group Psychotherapy'
            ],
            [
                'code' => '96101',
                'name' => 'Psych.testing by psychologist'
            ],
            [
                'code' => '96404',
                'name' => 'Preventive Counseling (EAP)'
            ],
        ];

        foreach ($procedures as $procedure) {
            $pr = new PatientInsuranceProcedure($procedure);
            $pr->save();
        }
    }
}

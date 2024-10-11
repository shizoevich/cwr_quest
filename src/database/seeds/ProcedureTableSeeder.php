<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcedureTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('patient_insurances_procedures')->insert([
            [
                'code' => '9027',
                'name' => 'Psychotherapy,60 min',
            ],
            [
                'code' => '90337',
                'name' => 'Preventive medicine counseling (EAP)',
            ],
            [
                'code' => '90557',
                'name' => 'Psychotherapy,45 min',
            ],
            [
                'code' => '97837',
                'name' => 'Initial Psychotherapy Eval.',
            ],
            [
                'code' => '87837',
                'name' => 'Group Psychotherapy',
            ],
        ]);
    }
}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PatientVisitsStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('patient_visit_statuses')->insert([
            [
                'name' => 'Claim Created Primary',
            ],
            [
                'name' => 'Open',
            ],
            [
                'name' => 'Cash Payment',
            ],
            [
                'name' => 'Billing Statement Sent',
            ],
            [
                'name' => 'Appeal',
            ],
            [
                'name' => 'Claim Created Secondary',
            ],
            [
                'name' => 'Ready For Claim',
            ],
        ]);
    }
}

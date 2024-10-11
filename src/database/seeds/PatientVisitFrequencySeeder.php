<?php

use App\PatientVisitFrequency;
use Illuminate\Database\Seeder;

class PatientVisitFrequencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = ['Twice a week', 'Weekly', 'Biweekly', 'Monthly'];

        foreach ($data as $name) {
            PatientVisitFrequency::firstOrCreate(['name' => $name]);
        }
    }
}

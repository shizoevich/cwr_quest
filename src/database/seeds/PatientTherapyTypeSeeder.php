<?php

use App\Models\PatientTherapyType;
use Illuminate\Database\Seeder;

class PatientTherapyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = ['Individual', 'Couples', 'Family therapy'];

        foreach ($types as $type) {
            PatientTherapyType::create(['name' => $type]);
        }
    }
}

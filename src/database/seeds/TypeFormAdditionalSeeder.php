<?php

use Illuminate\Database\Seeder;

class TypeFormAdditionalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(TherapistCategorySeeder::class);
        $this->call(TherapistEthnicitySeeder::class);
        $this->call(TherapistLanguageSeeder::class);
        $this->call(TherapistRaceSeeder::class);
        $this->call(TherapistSpecialtySeeder::class);
        $this->call(TherapistTreatmentTypeSeeder::class);
    }
}

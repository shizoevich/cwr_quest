<?php

use Illuminate\Database\Seeder;

class TypeFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(TherapistAgeGroupsSeeder::class);
        $this->call(TherapistTypeOfClientsSeeder::class);
//        Do not call this seeder!
//        $this->call(TherapistPracticeFocusSeeder::class);
        $this->call(TherapistPracticeFocusFirstSeeder::class);
        $this->call(TherapistPracticeFocusSecondSeeder::class);
        $this->call(TypeFormAdditionalSeeder::class);
    }
}

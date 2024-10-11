<?php

use Illuminate\Database\Seeder;

class TherapistTypeOfClientsSeeder extends Seeder
{
    private $labels = [
        'Individuals',
        'Couples',
        'Families',
        'Groups',
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Illuminate\Support\Facades\DB::delete('delete from therapist_survey_type_of_clients');

        array_walk($this->labels, function($label){
            \DB::table('therapist_survey_type_of_clients')->insert([
                'label' => $label,
            ]);
        });

    }
}

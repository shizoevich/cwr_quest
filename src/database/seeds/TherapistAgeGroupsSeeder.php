<?php

use Illuminate\Database\Seeder;

class TherapistAgeGroupsSeeder extends Seeder
{
    private $labels = [
        'Children 0-5 years old',
        'Children 6-12 years old',
        'Adolescents',
        'Young adults',
        'Middle age adults',
        'Older adults (Geriatric)',
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Illuminate\Support\Facades\DB::delete('delete from therapist_survey_age_groups');

        array_walk($this->labels, function($label){
            \DB::table('therapist_survey_age_groups')->insert([
                'label' => $label,
            ]);
        });

    }
}

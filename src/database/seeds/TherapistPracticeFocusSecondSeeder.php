<?php

use Illuminate\Database\Seeder;

class TherapistPracticeFocusSecondSeeder extends Seeder
{
    private $labels = [
        'Health-Disabilities',
        'Hearing Impaired',
        'HIV/AIDS',
        'Hypnotherapy',
        'Learning Disabilities',
        'Life Management Counseling',
        'Managed Disability',

        'Marriage and Family Therapy',
        'Men\'s Issues',
        'Mood Disorders',
        'Neuropsych Testing',
        'Obsessive-Compulsive Disorder',
        'Occupational Issues',
        'Pain Management',

        'Parenting Issues',
        'Personality Disorders',
        'Pharmacology-Medication Management',
        'Post-Traumatic Stress Disorder',
        'Psychological Testing',
        'Psychotic Disorders',
        'Sexual-Physical Abuse',

        'Sexual Dysfunction',
        'Stress Management',
        'Substance Abuse Professional -SAP-',
        'Women\'s Issues',
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Illuminate\Support\Facades\DB::delete('delete from therapist_survey_practice_focus_second');

        array_walk($this->labels, function($label){
            \DB::table('therapist_survey_practice_focus_second')->insert([
                'label' => $label,
            ]);
        });

    }
}

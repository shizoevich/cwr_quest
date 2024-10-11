<?php

use Illuminate\Database\Seeder;

class TherapistPracticeFocusFirstSeeder extends Seeder
{
    private $labels = [
        'Addiction Psychiatry',
        'Addiction Specialist',
        'Adolescent Psychiatry',
        'Anxiety Disorders',
        'Attention Deficit Disorder',
        'Bereavement-Grief Counseling',
        'Biofeedback',

        'Certified Employee Assistance Professional',
        'Child Abuse',
        'Child Psychiatry',
        'Christian Counseling',
        'Codependency Behavioral Therapy',
        'Cognitive Behavioral Therapy',
        'Co morbidity',

        'Crisis Intervention',
        'Detoxification',
        'Dialectic Behavioral Therapy',
        'Dissociative Disorder',
        'Domestic Violence',
        'Eating Disorders',
        'Gay-Lesbian Issues',

        'Gender Identity',
        'Geriatric Psychiatry',
        'Group Therapy',
        'Health-Disabilities',
        'Hearing Impaired',
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Illuminate\Support\Facades\DB::delete('delete from therapist_survey_practice_focus_first');

        array_walk($this->labels, function($label){
            \DB::table('therapist_survey_practice_focus_first')->insert([
                'label' => $label,
            ]);
        });

    }
}

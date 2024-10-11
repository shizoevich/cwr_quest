<?php

use Illuminate\Database\Seeder;

class TherapistPracticeFocusSeeder extends Seeder
{
    private $labels = [
        'Addiction Therapy',
        'Addiction Specialist',
        'Adolescent Therapy',
        'Anxiety Disorders',
        'Attention Deficit Disorder',
        'Bereavement-Grief Counseling',
        'Biofeedback',

        'Certified Employee Assistance Professional',
        'Child Abuse',
        'Child Therapy',
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
        'Geriatric Therapy',
        'Group Therapy',
        'Health-Disabilities',
        'Hearing Impaired',

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
        \Illuminate\Support\Facades\DB::delete('delete from therapist_survey_practice_focus');

        array_walk($this->labels, function($label){
            \DB::table('therapist_survey_practice_focus')->insert([
                'label' => $label,
            ]);
        });

    }
}

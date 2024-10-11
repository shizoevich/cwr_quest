<?php

use App\Models\Therapist\TherapistSurveySpecialty;
use App\TherapistSurvey;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class TherapistSpecialtySeeder extends Seeder
{
    private $specialties = [
        [
            'label' => 'Addiction Therapy',
            'tridiuum_value' => null,
        ],
        [
            'label' => 'Addiction Specialist',
            'tridiuum_value' => null,
        ],
        [
            'tridiuum_value' => '06be83ed-42b4-48d5-ba06-acbcb7cc3942',
            'label' => 'ADHD'
        ],
        [
            'tridiuum_value' => '69245839-5def-49b0-a444-0d5a70c78a3d',
            'label' => 'Adjustment Issues'
        ],
        [
            'label' => 'Adolescent Therapy',
            'tridiuum_value' => null,
        ],
        [
            'tridiuum_value' => 'aa3447c3-8b4e-4b3f-9595-03af3de00f5f',
            'label' => 'Adoption'
        ],
        [
            'tridiuum_value' => '74fd1fe9-e213-4c09-912e-7595ddb156bf',
            'label' => 'Anger Management'
        ],
        [
            'tridiuum_value' => '28710977-c05a-4f8b-87bf-3bd1d0323bae',
            'label' => 'Anxiety'
        ],
        [
            'tridiuum_value' => '20076eb9-56c3-4050-bbdf-3fdbaa56158c',
            'label' => 'Autism Spectrum'
        ],
        [
            'tridiuum_value' => 'd245ec08-13ab-4684-975d-e503a8821349',
            'label' => 'Behavioral Problems'
        ],
        [
            'label' => 'Bereavement-Grief Counseling',
            'tridiuum_value' => null,
        ],
        [
            'label' => 'Biofeedback',
            'tridiuum_value' => null,
        ],

        [
            'tridiuum_value' => '18e79e89-1ec5-4172-b955-6507541b0db3',
            'label' => 'Career Counseling'
        ],
        [
            'label' => 'Certified Employee Assistance Professional',
            'tridiuum_value' => null,
        ],
        [
            'label' => 'Child Abuse',
            'tridiuum_value' => null,
        ],
        [
            'label' => 'Child Therapy',
            'tridiuum_value' => null,
        ],
        [
            'label' => 'Christian Counseling',
            'tridiuum_value' => null,
        ],
        [
            'tridiuum_value' => '23191534-226b-4f0c-97a2-272ca4cc6aa7',
            'label' => 'Chronic Pain'
        ],
        [
            'label' => 'Codependency Behavioral Therapy',
            'tridiuum_value' => null,
        ],
        [
            'label' => 'Cognitive Behavioral Therapy',
            'tridiuum_value' => null,
        ],
        [
            'tridiuum_value' => 'b6252ea1-cbc2-4bef-99d7-de57414e99e5',
            'label' => 'Coping Skills'
        ],
        [
            'label' => 'Co morbidity',
            'tridiuum_value' => null,
        ],
        [
            'label' => 'Crisis Intervention',
            'tridiuum_value' => null,
        ],
        [
            'tridiuum_value' => 'dfc3b835-0e82-4820-958a-f4ecf52607f8',
            'label' => 'Dementia'
        ],
        [
            'tridiuum_value' => 'a2759116-771f-4117-94b5-fb1c7fca424f',
            'label' => 'Depression'
        ],
        [
            'label' => 'Detoxification',
            'tridiuum_value' => null,
        ],
        [
            'tridiuum_value' => '2ff89caa-6e02-4530-acfb-0f4160a02443',
            'label' => 'Developmental Disorders'
        ],
        [
            'label' => 'Dialectic Behavioral Therapy',
            'tridiuum_value' => null,
        ],
        [
            'tridiuum_value' => '6d1869aa-c67d-401c-bab0-5c9d61df04df',
            'label' => 'Dissociative Disorders'
        ],
        [
            'tridiuum_value' => '5b48346b-f569-429f-9da9-60876b31dae1',
            'label' => 'Divorce'
        ],
        [
            'tridiuum_value' => 'b11b8467-296c-414a-b902-f2eeaa295ac5',
            'label' => 'Domestic Violence'
        ],
        [
            'tridiuum_value' => '6240ad80-a4c2-4d38-86d6-7b3e19391145',
            'label' => 'Dual Diagnosis'
        ],
        [
            'tridiuum_value' => '0996bebd-8a3d-493f-8a8b-9f9aa7fc0e74',
            'label' => 'Eating Disorders'
        ],
        [
            'tridiuum_value' => '9076be88-08ab-4b04-82d1-e2b4eb28874b',
            'label' => 'Family Conflict'
        ],
        [
            'tridiuum_value' => '1fb3f498-98cf-46b0-ad09-62223c4f058c',
            'label' => 'Gambling Problem'
        ],
        [
            'label' => 'Gay-Lesbian Issues',
            'tridiuum_value' => null,
        ],
        [
            'tridiuum_value' => 'e1770864-3995-4042-b1f0-a7414c7f5887',
            'label' => 'Gender Identity Issues'
        ],
        [
            'label' => 'Geriatric Therapy',
            'tridiuum_value' => null,
        ],
        [
            'label' => 'Group Therapy',
            'tridiuum_value' => null,
        ],
        [
            'tridiuum_value' => 'ed3f9bdf-917c-4bdf-8290-f82ae84525c1',
            'label' => 'Grief and Loss'
        ],
        [
            'label' => 'Health-Disabilities',
            'tridiuum_value' => null,
        ],
        [
            'tridiuum_value' => '08ef96ab-9ade-41c0-ae08-d0f0a0bb6687',
            'label' => 'Hoarding'
        ],
        [
            'label' => 'Hearing Impaired',
            'tridiuum_value' => null,
        ],
        [
            'label' => 'HIV/AIDS',
            'tridiuum_value' => null,
        ],
        [
            'label' => 'Hypnotherapy',
            'tridiuum_value' => null,
        ],
        [
            'tridiuum_value' => 'a5133721-e34a-4971-b673-f161af11abc4',
            'label' => 'Infertility'
        ],
        [
            'tridiuum_value' => '37d13a63-1d0c-4e68-8de0-7af693a37140',
            'label' => 'Learning Disabilities'
        ],
        [
            'tridiuum_value' => '897d1e7f-26b1-4b2f-9178-2e1fb9b1c4fc',
            'label' => 'Life Coaching'
        ],
        [
            'label' => 'Life Management Counseling',
            'tridiuum_value' => null,
        ],
        [
            'tridiuum_value' => '64d880ee-e27d-4e42-b183-6d528612155d',
            'label' => 'Life Transitions'
        ],
        [
            'label' => 'Managed Disability',
            'tridiuum_value' => null,
        ],
        [
            'tridiuum_value' => 'cf7a34b7-3c82-4595-97e8-d0bd65525433',
            'label' => 'Marital/Premarital'
        ],
        [
            'label' => 'Marriage and Family Therapy',
            'tridiuum_value' => null,
        ],
        [
            'tridiuum_value' => '77c2d240-a54a-454c-9065-310f5bed194f',
            'label' => 'Medical Problems/Illness'
        ],
        [
            'tridiuum_value' => '5b7b9c9b-933c-4895-972d-83441ae2742c',
            'label' => 'Men’s Issues'
        ],
        [
            'tridiuum_value' => '65eae3f5-878f-4a7b-91ea-fc0a996e3531',
            'label' => 'Mood Disorders'
        ],
        [
            'label' => 'Neuropsych Testing',
            'tridiuum_value' => null,
        ],
        [
            'tridiuum_value' => 'cd049a1a-7843-4036-af54-adaa0e18f175',
            'label' => 'Obsessive-Compulsive Disorder'
        ],
        [
            'label' => 'Occupational Issues',
            'tridiuum_value' => null,
        ],
        [
            'tridiuum_value' => 'd8dde005-506b-4383-8d21-81ec728a8220',
            'label' => 'Parenting'
        ],
        [
            'tridiuum_value' => '3fd1b629-ded1-4812-be4a-391b180c3b33',
            'label' => 'Personality Disorders'
        ],
        [
            'label' => 'Pharmacology-Medication Management',
            'tridiuum_value' => null,
        ],
        [
            'tridiuum_value' => '92d0ce60-9ffa-4bc5-b587-2c5bf5384aa2',
            'label' => 'Pregnancy/Postpartum'
        ],
        [
            'label' => 'Psychological Testing',
            'tridiuum_value' => null,
        ],
        [
            'label' => 'Psychotic Disorders',
            'tridiuum_value' => null,
        ],
        [
            'tridiuum_value' => '8c208108-48ff-4a6c-9cf7-ce416e46b1e5',
            'label' => 'PTSD'
        ],
        [
            'label' => 'Pain Management',
            'tridiuum_value' => null,
        ],
        [
            'tridiuum_value' => '37939a00-8c1b-4c13-a4da-c4629374f1a7',
            'label' => 'Relationship Issues'
        ],
        [
            'tridiuum_value' => 'e0304797-f298-4947-a663-650a7296e5a3',
            'label' => 'School Problems'
        ],
        [
            'tridiuum_value' => '58dd240f-4cf7-4f63-b64c-ba0a3333853e',
            'label' => 'Selective Mutism'
        ],
        [
            'tridiuum_value' => 'c852ab9d-0a79-4a1d-a23d-a244ba13dfb2',
            'label' => 'Self-esteem'
        ],
        [
            'tridiuum_value' => '140401c7-008a-4882-b27f-d171b7353a00',
            'label' => 'Self-Harming'
        ],
        [
            'tridiuum_value' => '6e48b156-d56c-4061-9f6b-169bf622633d',
            'label' => 'Sexual Addiction'
        ],
        [
            'tridiuum_value' => '593d9915-016f-4b45-a818-a09044dc7bd8',
            'label' => 'Sexual Dysfunction'
        ],
        [
            'label' => 'Sexual-Physical Abuse',
            'tridiuum_value' => null,
        ],
        [
            'tridiuum_value' => 'e6990537-5dd5-437b-a75f-81ae4e038e45',
            'label' => 'Sleep/Insomnia'
        ],
        [
            'tridiuum_value' => '1e8e8d83-b7ab-4392-8c7f-f04515b49e93',
            'label' => 'Spirituality'
        ],
        [
            'label' => 'Stress Management',
            'tridiuum_value' => null,
        ],
        [
            'label' => 'Substance Abuse Professional -SAP-',
            'tridiuum_value' => null,
        ],
        [
            'tridiuum_value' => 'b977f596-9c6d-4a1f-b292-0abfa13d2e48',
            'label' => 'Substance Use Issues'
        ],
        [
            'tridiuum_value' => 'b6751bfa-005c-41b3-b1a0-1c028a8d14f6',
            'label' => 'Testing & Evaluation'
        ],
        [
            'tridiuum_value' => '7917900a-b670-4dbc-beca-28f55e12cafe',
            'label' => 'TIC'
        ],
        [
            'tridiuum_value' => 'f3431630-f1ba-4727-93b3-0ab8978f7f4d',
            'label' => 'TMS'
        ],
        [
            'tridiuum_value' => '419f4874-0332-4892-b1fa-5f8deec00d99',
            'label' => 'Trauma'
        ],
        [
            'tridiuum_value' => '7a051117-cf18-407c-b8c1-58b8a65a6277',
            'label' => 'Trichotillomania'
        ],
        [
            'tridiuum_value' => '7a06bcf5-b6ad-44ac-aae3-444a9d95a138',
            'label' => 'Women’s Issues'
        ],
        [
            'tridiuum_value' => '304131a8-5944-42a0-8a02-12556ffd3490',
            'label' => '*Other'
        ],
    ];

    private $practiceFocusParseExceptions = [
        'Attention Deficit Disorder' => 'ADHD',
        'Anxiety Disorders' => 'Anxiety',
        'Dissociative Disorder' => 'Dissociative Disorders',
        'Gender Identity' => 'Gender Identity Issues',
        'Parenting Issues' => 'Parenting',
        'Post-Traumatic Stress Disorder' => 'PTSD',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TherapistSurveySpecialty::query()->delete();

        TherapistSurveySpecialty::insert($this->specialties);

        if (Schema::hasTable('therapist_survey_practice_focus')) {
            $specialties = TherapistSurveySpecialty::query()
                ->select(['id', 'label'])
                ->get();

            $therapists = TherapistSurvey::query()
                ->with([
                    'practiceFocus',
                    'practiceFocusSecond',
                ])
                ->get();

            $therapists->each(function ($therapist) use ($specialties) {
                $therapist->practiceFocus->each(function ($practiceFocus) use ($therapist, $specialties) {
                    if (array_key_exists($practiceFocus->label, $this->practiceFocusParseExceptions)) {
                        $specialty = $specialties->where('label', $this->practiceFocusParseExceptions[$practiceFocus->label])->first();
                    } else {
                        $specialty = $specialties->where('label', $practiceFocus->label)->first();
                    }

                    if ($specialty) {
                        $therapist->specialties()->attach($specialty->id);
                    }
                });

                $therapist->practiceFocusSecond->each(function ($practiceFocus) use ($therapist, $specialties) {
                    if (array_key_exists($practiceFocus->label, $this->practiceFocusParseExceptions)) {
                        $specialty = $specialties->where('label', $this->practiceFocusParseExceptions[$practiceFocus->label])->first();
                    } else {
                        $specialty = $specialties->where('label', $practiceFocus->label)->first();
                    }

                    if ($specialty) {
                        $therapist->specialties()->attach($specialty->id);
                    }
                });
            });
        }
    }
}

<?php

use App\Models\Therapist\TherapistSurveyTreatmentType;
use Illuminate\Database\Seeder;

class TherapistTreatmentTypeSeeder extends Seeder
{
    private $treatment_types = [
        [
            'tridiuum_value' => 'b31ca52f-2ee6-4e17-8ea3-016d22659b25',
            'label' => 'Acceptance & Commitment Therapy (ACT)'
        ],
        [
            'tridiuum_value' => 'b5d37304-14ba-4777-9ac7-69a512c26028',
            'label' => 'Behavior Therapy'
        ],
        [
            'tridiuum_value' => '329c9bdd-8a67-4868-b642-e618a97a87c7',
            'label' => 'Cognitive-Behavioral Therapy (CBT)'
        ],
        [
            'tridiuum_value' => '41a149c4-8392-454a-9cb0-98b70f0b0b75',
            'label' => 'Cognitive processing therapy for PTSD (CPT)'
        ],
        [
            'tridiuum_value' => '150190a1-4351-4f5d-b7c2-8ad0a30f343b',
            'label' => 'Dialectical Behavior Therapy (DBT)'
        ],
        [
            'tridiuum_value' => 'd33c61d1-e902-4c04-bd19-4f1c3bbbb00a',
            'label' => 'Eclectic'
        ],
        [
            'tridiuum_value' => 'e4b73557-a08d-4bc3-b298-e5f130b24a28',
            'label' => 'EMDR'
        ],
        [
            'tridiuum_value' => '9d681460-cd38-4866-ba15-511f570ba424',
            'label' => 'Exposure Response Prevention Therapy'
        ],
        [
            'tridiuum_value' => '2973a65b-6c1c-46eb-81af-9b4cb2c4cd01',
            'label' => 'Faith Based – Buddhist'
        ],
        [
            'tridiuum_value' => '174137a2-ed57-4ba3-8089-d1977baff06e',
            'label' => 'Faith Based – Christian'
        ],
        [
            'tridiuum_value' => '0384634c-fc13-4dca-b220-fdd2f12c7805',
            'label' => 'Faith Based – Hinduism'
        ],
        [
            'tridiuum_value' => '63c44e28-0459-4a53-86e8-f66a9b1f4b4e',
            'label' => 'Faith Based – Islam (Muslim)'
        ],
        [
            'tridiuum_value' => '2bb295b2-4fe5-4e36-bd43-1b0a6b4a4156',
            'label' => 'Faith Based – Jewish'
        ],
        [
            'tridiuum_value' => '68c7bbb3-0609-4a44-862d-c782a64693ae',
            'label' => 'Family/Marital'
        ],
        [
            'tridiuum_value' => '27af9413-ff6d-42ff-8de4-c1dca7d592e4',
            'label' => 'Family Systems'
        ],
        [
            'tridiuum_value' => '7b1bef87-28d7-451f-aa8b-c20cb399e8a7',
            'label' => 'Humanistic Therapy'
        ],
        [
            'tridiuum_value' => '4b9678d6-fccb-4393-b537-c045110b62cf',
            'label' => 'Integrative or Holistic Therapy'
        ],
        [
            'tridiuum_value' => '57480c95-0a3e-4ad6-980d-af7d78894e3f',
            'label' => 'Interpersonal'
        ],
        [
            'tridiuum_value' => 'f5b03aed-8e06-4d82-9ce1-67bafb25d786',
            'label' => 'Mindfulness-Based (MBCT)'
        ],
        [
            'tridiuum_value' => '8a8ef67d-b91f-4f1f-ace0-f88176fdd3e7',
            'label' => 'Person/Client-Centered Counseling'
        ],
        [
            'tridiuum_value' => 'c7b89556-7d01-4b36-b367-d9fa0fa94a20',
            'label' => 'Play Therapy'
        ],
        [
            'tridiuum_value' => 'a3df5560-9a93-4477-93cd-237427d50a31',
            'label' => 'Psychodynamic'
        ],
        [
            'tridiuum_value' => 'cce4498a-c993-46bd-8b08-d7643050dc15',
            'label' => 'Psychological testing & evaluation'
        ],
        [
            'tridiuum_value' => '5cb33ab0-cbd4-4b64-9116-2abd9e284dae',
            'label' => 'Relational'
        ],
        [
            'tridiuum_value' => '14f05645-a1ff-452c-b10e-89c04ff54512',
            'label' => 'Sex therapy'
        ],
        [
            'tridiuum_value' => '4ca3243b-16c2-4c31-b0c2-6555d78af7bf',
            'label' => 'Structural Family Therapy'
        ],
        [
            'tridiuum_value' => '75e26067-1873-4d17-a3ff-e3a263e88ab5',
            'label' => 'Trauma Focused'
        ]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TherapistSurveyTreatmentType::query()->delete();

        TherapistSurveyTreatmentType::query()->insert($this->treatment_types);
    }
}

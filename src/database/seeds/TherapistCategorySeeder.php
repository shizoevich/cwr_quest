<?php

use App\Models\Therapist\TherapistSurveyPatientCategory;
use Illuminate\Database\Seeder;

class TherapistCategorySeeder extends Seeder
{
    private $categories = [
        [
            'tridiuum_value' => 'e575c90d-5c72-46cb-b07b-eff9579c4e72',
            'label' => 'Cancer',
        ],
        [
            'tridiuum_value' => 'baa44cec-2afb-4cca-8893-2a03960c9d5f',
            'label' => 'Faith Based',
        ],
        [
            'tridiuum_value' => 'd30dcac6-fe2c-4d36-9b8d-34eca1492fc7',
            'label' => 'HIV/AIDS Clients',
        ],
        [
            'tridiuum_value' => '7e7bf664-b090-406d-aa13-4efcb23a66a5',
            'label' => 'LGBTQIA Clients',
        ],
        [
            'tridiuum_value' => 'caebd000-0900-4ca6-8f10-eba016f08e47',
            'label' => 'Veterans/Military',
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TherapistSurveyPatientCategory::query()->delete();

        TherapistSurveyPatientCategory::query()->insert($this->categories);
    }
}

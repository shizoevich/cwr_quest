<?php

use App\Models\Patient\Inquiry\PatientInquiryStage;
use App\Models\Patient\PatientTag;
use Illuminate\Database\Seeder;

class PatientTagSeeder extends Seeder
{
    private $tags = [
        [
            'tag' => PatientTag::PATIENT_TAG_TRANSFERRING,
            'hex_text_color' => '#002eae',
            'hex_background_color' => '#69caf9',
        ],
        [
            'tag' => PatientTag::PATIENT_TAG_RETURNING,
            'hex_text_color' => '#0e694f',
            'hex_background_color' => '#4ade80',
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->tags as $tag) {
            PatientTag::updateOrCreate(
                [
                    'tag' => $tag['tag'],
                ],
                [
                'hex_text_color' => $tag['hex_text_color'],
                'hex_background_color' => $tag['hex_background_color'],
                'is_system' => 1,
                'created_by' => null,
            ]);
        }
    }
}
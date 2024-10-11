<?php

use App\Models\Patient\Inquiry\PatientInquiryStage;
use Illuminate\Database\Seeder;

class PatientInquiryStageSeeder extends Seeder
{
    private $stages = [
        [
            'name' => PatientInquiryStage::STAGE_INBOX,
        ],
        [
            'name' => PatientInquiryStage::STAGE_IN_PROGRESS,
        ],
        [
            'name' => PatientInquiryStage::STAGE_APPOINTMENT_SCHEDULED,
        ],
        [
            'name' => PatientInquiryStage::STAGE_ONBOARDING_COMPLETE,
        ],
        [
            'name' => PatientInquiryStage::STAGE_INITIAL_APPOINTMENT_COMPLETE,
        ],
        [
            'name' => PatientInquiryStage::STAGE_INITIAL_SURVEY_COMPLETE,
        ],
        [
            'name' => PatientInquiryStage::STAGE_FOUR_APPOINTMENTS_COMPLETE,
        ],
        [
            'name' => PatientInquiryStage::STAGE_ON_HOLD,
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->stages as $stage) {
            PatientInquiryStage::updateOrCreate(
                ['name' => $stage['name']],
            );
        }
    }
}

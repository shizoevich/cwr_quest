<?php

use App\AssessmentForm;
use Illuminate\Database\Seeder;

class AssessmentFormsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $initialAssessment = AssessmentForm::firstOrCreate([
            'title' => 'Initial Assessment',
        ])->id;
        $initialAssessmentCWR = AssessmentForm::firstOrCreate([
            'title' => 'CWR',
            'parent' => $initialAssessment,
        ])->id;
        AssessmentForm::firstOrCreate([
            'title' => 'CWR Initial Assessment',
            'document_name' => 'CWR Initial Assessment',
            'slug' => 'cwr-initial-assessment',
            'group_id' => 1,
            'file_name' => 'cwr_initial_assessment.docx',
            'type' => AssessmentForm::INITIAL_ASSESSMENT_TYPE,
            'parent' => $initialAssessmentCWR,
            'has_signature' => true,
        ]);
        $initialAssessmentKaiser = AssessmentForm::firstOrCreate([
            'title' => 'Kaiser',
            'parent' => $initialAssessment,
            'ind' => 2,
        ])->id;
        AssessmentForm::firstOrCreate([
            'title' => 'KP Initial Assessment (Adult) - Panorama City',
            'document_name' => 'KP Initial Assessment (Adult) - Panorama City',
            'slug' => 'kp-initial-assessment-adult-pc',
            'group_id' => 2,
            'file_name' => 'kp_initial_assessment_adult_panorama_city.docx',
            'type' => AssessmentForm::INITIAL_ASSESSMENT_TYPE,
            'parent' => $initialAssessmentKaiser,
            'password' => 'CWR2015',
        ]);
        AssessmentForm::firstOrCreate([
            'title' => 'KP Initial Assessment (Child) - Panorama City',
            'document_name' => 'KP Initial Assessment (Child) - Panorama City',
            'slug' => 'kp-initial-assessment-child-pc',
            'group_id' => 2,
            'file_name' => 'kp_initial_assessment_child_panorama_city.docx',
            'type' => AssessmentForm::INITIAL_ASSESSMENT_TYPE,
            'parent' => $initialAssessmentKaiser,
            'has_signature' => true,
            'ind' => 2,
            'password' => 'CWR2015',
        ]);
        AssessmentForm::firstOrCreate([
            'title' => 'KP Initial Assessment (Adult) - Woodland Hills',
            'document_name' => 'KP Initial Assessment (Adult) - Woodland Hills',
            'slug' => 'kp-initial-assessment-adult-wh',
            'group_id' => 2,
            'file_name' => 'kp_initial_assessment_adult_woodland_hills.docx',
            'type' => AssessmentForm::INITIAL_ASSESSMENT_TYPE,
            'parent' => $initialAssessmentKaiser,
            'ind' => 3,
            'password' => 'kaiserjk',
        ]);
        AssessmentForm::firstOrCreate([
            'title' => 'KP Initial Assessment (Child) - Woodland Hills',
            'document_name' => 'KP Initial Assessment (Child) - Woodland Hills',
            'slug' => 'kp-initial-assessment-child-wh',
            'group_id' => 2,
            'file_name' => 'kp_initial_assessment_child_woodland_hills.docx',
            'type' => AssessmentForm::INITIAL_ASSESSMENT_TYPE,
            'parent' => $initialAssessmentKaiser,
            'ind' => 4,
            'password' => 'kaiserjk',
        ]);
        AssessmentForm::firstOrCreate([
            'title' => 'KP Initial Assessment (Adult) - Los Angeles',
            'document_name' => 'KP Initial Assessment (Adult) - Los Angeles',
            'slug' => 'kp-initial-assessment-adult-la',
            'group_id' => 2,
            'file_name' => 'kp_initial_assessment_adult_los_angeles.docx',
            'type' => AssessmentForm::INITIAL_ASSESSMENT_TYPE,
            'parent' => $initialAssessmentKaiser,
            'ind' => 5,
            'password' => 'CWR2015',
        ]);
        AssessmentForm::firstOrCreate([
            'title' => 'KP Initial Assessment (Child) - Los Angeles',
            'document_name' => 'KP Initial Assessment (Child) - Los Angeles',
            'slug' => 'kp-initial-assessment-child-la',
            'group_id' => 2,
            'file_name' => 'kp_initial_assessment_child_los_angeles.docx',
            'type' => AssessmentForm::INITIAL_ASSESSMENT_TYPE,
            'parent' => $initialAssessmentKaiser,
            'ind' => 6,
            'password' => 'CWR2015',
        ]);

        $requestForReauth = AssessmentForm::firstOrCreate([
            'title' => 'Request for Reauthorization',
            'parent' => 0,
            'ind' => 2,
        ])->id;
        AssessmentForm::firstOrCreate([
            'title' => 'Request for Reauthorization - PGBA TriWest VA CCN',
            'document_name' => 'Request for Reauthorization - PGBA TriWest VA CCN',
            'slug' => 'va-request-for-reauthorization',
            'group_id' => 19,
            'file_name' => 'va_request_for_reauthorization.docx',
            'type' => AssessmentForm::REQUEST_FOR_REAUTHORIZATION_TYPE,
            'parent' => $requestForReauth,
            'has_signature' => true,
            'ind' => 1,
            'meta' => '{"signatureWidth":72,"signatureHeight":48,"checkboxSize":8,"removeFaxCover":false}'
        ]);

        $dischargeSummary = AssessmentForm::firstOrCreate([
            'title' => 'Discharge Summary',
            'parent' => 0,
            'ind' => 3,
        ])->id;
        $dischargeSummaryCWR = AssessmentForm::firstOrCreate([
            'title' => 'CWR',
            'parent' => $dischargeSummary,
        ])->id;
        AssessmentForm::firstOrCreate([
            'title' => 'CWR Patient Discharge Summary',
            'slug' => 'cwr-patient-discharge-summary',
            'group_id' => 4,
            'file_name' => 'cwr_patient_discharge_summary.docx',
            'type' => AssessmentForm::DISCHARGE_SUMMARY_TYPE,
            'parent' => $dischargeSummaryCWR,
            'has_signature' => true,
        ]);
        $dischargeSummaryKaiser = AssessmentForm::firstOrCreate([
            'title' => 'Kaiser',
            'parent' => $dischargeSummary,
            'ind' => 2,
        ])->id;
        AssessmentForm::firstOrCreate([
            'title' => 'KP Patient Discharge Summary - Panorama City',
            'slug' => 'kp-patient-discharge-summary',
            'group_id' => 5,
            'file_name' => 'kp_patient_discharge_summary.docx',
            'type' => AssessmentForm::DISCHARGE_SUMMARY_TYPE,
            'parent' => $dischargeSummaryKaiser,
            'has_signature' => true,
            'password' => 'CWR2015',
        ]);
        AssessmentForm::firstOrCreate([
            'title' => 'KP Patient Discharge Summary - Woodland Hills',
            'document_name' => 'KP Patient Discharge Summary - Woodland Hills',
            'slug' => 'kp-patient-discharge-summary-wh',
            'group_id' => 5,
            'file_name' => 'kp_2_subsequent_requests_for_reauthorization_woodland_hills.docx',
            'type' => AssessmentForm::DISCHARGE_SUMMARY_TYPE,
            'parent' => $dischargeSummaryKaiser,
            'ind' => 2,
            'password' => 'kaiserjk',
        ]);
        AssessmentForm::firstOrCreate([
            'title' => 'KP Patient Discharge Summary - Los Angeles',
            'document_name' => 'KP Patient Discharge Summary - Los Angeles',
            'slug' => 'kp-patient-discharge-summary-la',
            'group_id' => 5,
            'file_name' => 'kp_2_subsequent_requests_for_reauthorization_los_angeles.docx',
            'type' => AssessmentForm::DISCHARGE_SUMMARY_TYPE,
            'parent' => $dischargeSummaryKaiser,
            'ind' => 3,
            'password' => 'CWR2015',
        ]);
        $requestForReferall = AssessmentForm::firstOrCreate([
            'title' => 'Request for Referral for Returning Patients',
            'parent' => 0,
            'ind' => 5,
        ])->id;
        $requestForReferallKaiser = AssessmentForm::firstOrCreate([
            'title' => 'Kaiser',
            'parent' => $requestForReferall,
        ])->id;
        AssessmentForm::firstOrCreate([
            'title' => 'KP Behavioral Health - Panorama City',
            'document_name' => 'KP Behavioral Health - Panorama City',
            'slug' => 'kp-behavioral-health-pc',
            'group_id' => 7,
            'file_name' => 'kp_request_for_referral_for_return_patients_panorama_city.docx',
            'parent' => $requestForReferallKaiser,
            'has_signature' => true,
            'password' => 'kaiserjk',
        ]);
        $medicationEvaluationReferral = AssessmentForm::firstOrCreate([
            'title' => 'Medication Evaluation Referral',
            'parent' => 0,
            'ind' => 6,
        ])->id;
        AssessmentForm::firstOrCreate([
            'title' => 'Kaiser Woodland Hills',
            'document_name' => 'Medication Evaluation Referral - Kaiser Woodland Hills',
            'slug' => 'kp-medication-evaluation-referral-wh',
            'group_id' => 8,
            'file_name' => 'kp_medication_evaluation_referral_woodland_hills.docx',
            'parent' => $medicationEvaluationReferral,
            'has_signature' => false,
            'password' => 'kaiserjk',
        ]);
        AssessmentForm::firstOrCreate([
            'title' => 'Kaiser Panorama City',
            'document_name' => 'Medication Evaluation Referral - Kaiser Panorama City',
            'slug' => 'kp-medication-evaluation-referral-pc',
            'group_id' => 8,
            'file_name' => 'kp_medication_evaluation_referral_panorama_city.docx',
            'parent' => $medicationEvaluationReferral,
            'has_signature' => false,
            'password' => 'CWR2015',
            'ind' => 2,
        ]);
        AssessmentForm::firstOrCreate([
            'title' => 'Kaiser Los Angeles',
            'document_name' => 'Medication Evaluation Referral - Kaiser Los Angeles',
            'slug' => 'kp-medication-evaluation-referral-la',
            'group_id' => 8,
            'file_name' => 'kp_medication_evaluation_referral_los_angeles.docx',
            'parent' => $medicationEvaluationReferral,
            'has_signature' => false,
            'password' => 'CWR2015',
            'ind' => 3,
        ]);
        
        AssessmentForm::firstOrCreate([
            'title' => 'Kaiser Referral to Behavioral Health Intensive Outpatient Services Woodland Hills',
            'document_name' => 'Referral to Behavioral Health Intensive Outpatient Services - Kaiser Woodland Hills',
            'slug' => 'kp-bhios-wh',
            'group_id' => 8,
            'file_name' => 'kp_bhios_woodland_hills.docx',
            'parent' => $medicationEvaluationReferral,
            'has_signature' => false,
            'password' => 'kaiserjk',
            'ind' => 3,
        ]);
    
        $referralForGroups = AssessmentForm::firstOrCreate([
            'title' => 'Referral for Groups',
            'parent' => 0,
            'ind' => 7,
        ])->id;
    
        AssessmentForm::firstOrCreate([
            'title' => 'Kaiser Los Angeles',
            'document_name' => 'Referral for Groups - Kaiser Los Angeles',
            'slug' => 'kp-ref-for-groups-la',
            'group_id' => 10,
            'file_name' => 'kp_referral_for_groups_los_angeles.docx',
            'parent' => $referralForGroups,
            'has_signature' => false,
            'password' => 'CWR2015',
            'ind' => 1,
        ]);
    
        $referralToHigherLevelOfCare = AssessmentForm::firstOrCreate([
            'title' => 'Referral to Higher Level of Care',
            'parent' => 0,
            'ind' => 8,
        ])->id;
    
        AssessmentForm::firstOrCreate([
            'title' => 'Kaiser Los Angeles',
            'document_name' => 'Referral to Higher Level of Care - Kaiser Los Angeles',
            'slug' => 'kp-ref-to-hloc-la',
            'group_id' => 11,
            'file_name' => 'kp_referral_to_hloc_los_angeles.docx',
            'parent' => $referralToHigherLevelOfCare,
            'has_signature' => false,
            'password' => 'CWR2015',
            'ind' => 1,
        ]);
    }
}

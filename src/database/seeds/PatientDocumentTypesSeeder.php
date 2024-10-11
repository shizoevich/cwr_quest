<?php

use App\PatientDocumentType;
use Illuminate\Database\Seeder;

class PatientDocumentTypesSeeder extends Seeder
{
    
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //fix typo
        PatientDocumentType::query()->where('type', 'Initial Assesment Tridiuum')->update([
            'type' => 'Initial Assessment Tridiuum',
        ]);
        PatientDocumentType::query()->where('type', 'New Patient - Patient Information / Informed Consent / Privacy Notice')->update([
            'type' => 'Patient Information / Informed Consent / Privacy Notice',
        ]);
        
        
        $parentIndex = 1;
        PatientDocumentType::updateOrCreate([
            'type' => 'Patient Information / Informed Consent / Privacy Notice',
        ], [
            'clickable' => true,
            'ind'       => $parentIndex++,
        ]);
    
        PatientDocumentType::updateOrCreate([
            'type' => "Payment for Service and Fee Arrangements",
        ], [
            'clickable'      => true,
            'ind'            => $parentIndex++,
            'only_for_admin' => false,
        ]);
    
        PatientDocumentType::updateOrCreate([
            'type' => "Agreement for Service & HIPAA Privacy Notice & Patient Rights & Notice to Psychotherapy Clients",
        ], [
            'clickable'      => true,
            'ind'            => $parentIndex++,
            'only_for_admin' => false,
        ]);
        
        PatientDocumentType::updateOrCreate([
            'type' => 'Authorization to Release Confidential Information',
        ], [
            'clickable' => true,
            'ind'       => $parentIndex++,
        ]);
    
        PatientDocumentType::updateOrCreate([
            'type' => "Telehealth Consent Form",
        ], [
            'clickable'      => true,
            'ind'            => $parentIndex++,
            'only_for_admin' => false,
        ]);
    
    
        //-------------------- [ Supporting Document ] --------------------
        $supportingDocumentId = PatientDocumentType::updateOrCreate([
            'type' => "Supporting Document",
        ], [
            'clickable'      => false,
            'ind'            => $parentIndex++,
            'only_for_admin' => false,
        ])->id;
        PatientDocumentType::updateOrCreate([
            'type' => "Insurance",
            'parent' => $supportingDocumentId,
        ], [
            'clickable'      => true,
            'ind'            => 1,
            'only_for_admin' => false,
        ]);
        PatientDocumentType::updateOrCreate([
            'type' => "Driver's License",
            'parent' => $supportingDocumentId,
        ], [
            'clickable'      => true,
            'ind'            => 2,
            'only_for_admin' => false,
        ]);
        
        
        //-------------------- [ Initial Assessment ] --------------------
        $initialAssessment = PatientDocumentType::updateOrCreate([
            'type' => 'Initial Assessment',
        ], [
            'ind'            => $parentIndex++,
            'only_for_admin' => true,
        ])->id;
        $initialAssessmentCWR = PatientDocumentType::updateOrCreate([
            'type'   => 'CWR',
            'parent' => $initialAssessment,
        ], [
            'only_for_admin' => true,
            'ind'            => 1,
        ])->id;
        PatientDocumentType::updateOrCreate([
            'type'   => 'CWR Initial Assessment',
            'parent' => $initialAssessmentCWR,
        ], [
            'clickable'      => true,
            'only_for_admin' => true,
            'ind'            => 1,
        ]);
        $initialAssessmentKaiser = PatientDocumentType::updateOrCreate([
            'type'   => 'Kaiser',
            'parent' => $initialAssessment,
        ], [
            'ind'            => 2,
            'only_for_admin' => true,
        ])->id;
        PatientDocumentType::updateOrCreate([
            'type'   => 'KP Initial Assessment (Adult) - Panorama City',
            'parent' => $initialAssessmentKaiser,
        ], [
            'clickable'      => true,
            'only_for_admin' => true,
            'ind'            => 1,
        ]);
        PatientDocumentType::updateOrCreate([
            'type'   => 'KP Initial Assessment (Child) - Panorama City',
            'parent' => $initialAssessmentKaiser,
        ], [
            'clickable'      => true,
            'ind'            => 2,
            'only_for_admin' => true,
        ]);
        PatientDocumentType::updateOrCreate([
            'type'   => 'KP Initial Assessment (Adult) - Woodland Hills',
            'parent' => $initialAssessmentKaiser,
        ], [
            'clickable'      => true,
            'ind'            => 3,
            'only_for_admin' => true,
        ]);
        PatientDocumentType::updateOrCreate([
            'type'   => 'KP Initial Assessment (Child) - Woodland Hills',
            'parent' => $initialAssessmentKaiser,
        ], [
            'clickable'      => true,
            'ind'            => 4,
            'only_for_admin' => true,
        ]);
        PatientDocumentType::updateOrCreate([
            'type'   => 'KP Initial Assessment (Adult) - Los Angeles',
            'parent' => $initialAssessmentKaiser,
        ], [
            'clickable'      => true,
            'ind'            => 5,
            'only_for_admin' => true,
        ]);
        PatientDocumentType::updateOrCreate([
            'type'   => 'KP Initial Assessment (Child) - Los Angeles',
            'parent' => $initialAssessmentKaiser,
        ], [
            'clickable'      => true,
            'ind'            => 6,
            'only_for_admin' => true,
        ]);
        
        //-------------------- [ Request for Reauthorization ] --------------------
        $requestForReauth = PatientDocumentType::updateOrCreate([
            'type' => 'Request for Reauthorization',
        ], [
            'parent'    => 0,
            'clickable' => false,
            'ind'       => $parentIndex++,
        ])->id;
        PatientDocumentType::updateOrCreate([
            'type'   => 'Request for Reauthorization - PGBA TriWest VA CCN',
            'parent' => $requestForReauth,
        ], [
            'type_id'   => PatientDocumentType::REQUEST_FOR_REAUTHORIZATION_TYPE,
            'clickable' => true,
            'ind'       => 1,
        ]);
        
        //-------------------- [ Discharge Summary ] --------------------
        $dischargeSummary = PatientDocumentType::updateOrCreate([
            'type' => 'Discharge Summary',
        ], [
            'parent'    => 0,
            'clickable' => false,
            'ind'       => $parentIndex++,
        ])->id;
        $dischargeSummaryCWR = PatientDocumentType::updateOrCreate([
            'type'   => 'CWR',
            'parent' => $dischargeSummary,
        ], [
            'clickable' => false,
            'ind'       => 1,
        ])->id;
        PatientDocumentType::updateOrCreate([
            'type'   => 'CWR Patient Discharge Summary',
            'parent' => $dischargeSummaryCWR,
        ], [
            'type_id'   => PatientDocumentType::DISCHARGE_SUMMARY_TYPE,
            'clickable' => true,
            'ind'       => 1,
        ]);
        $dischargeSummaryKaiser = PatientDocumentType::updateOrCreate([
            'type'   => 'Kaiser',
            'parent' => $dischargeSummary,
        ], [
            'clickable' => false,
            'ind'       => 2,
        ])->id;
        PatientDocumentType::updateOrCreate([
            'type'   => 'KP Patient Discharge Summary - Panorama City',
            'parent' => $dischargeSummaryKaiser,
        ], [
            'type_id'   => PatientDocumentType::DISCHARGE_SUMMARY_TYPE,
            'clickable' => true,
            'ind'       => 1,
        ]);
        PatientDocumentType::updateOrCreate([
            'type'   => 'KP Patient Discharge Summary - Woodland Hills',
            'parent' => $dischargeSummaryKaiser,
        ], [
            'type_id'   => PatientDocumentType::DISCHARGE_SUMMARY_TYPE,
            'clickable' => true,
            'ind'       => 2,
        ]);
        PatientDocumentType::updateOrCreate([
            'type'   => 'KP Patient Discharge Summary - Los Angeles',
            'parent' => $dischargeSummaryKaiser,
        ], [
            'type_id'   => PatientDocumentType::DISCHARGE_SUMMARY_TYPE,
            'clickable' => true,
            'ind'       => 3,
        ]);
        
        
        
        //-------------------- [  ] --------------------
        PatientDocumentType::updateOrCreate([
            'type' => "Patient's Extended Signature Authorization",
        ], [
            'clickable'      => true,
            'ind'            => $parentIndex++,
            'only_for_admin' => true,
        ]);
        
        PatientDocumentType::updateOrCreate([
            'type' => "Patient Rights",
        ], [
            'clickable'      => true,
            'ind'            => $parentIndex++,
            'only_for_admin' => true,
        ]);
        
        PatientDocumentType::updateOrCreate([
            'type' => "Informed Consent - Old Version (5 pages)",
        ], [
            'clickable'      => true,
            'ind'            => $parentIndex++,
            'only_for_admin' => true,
        ]);
        
        PatientDocumentType::updateOrCreate([
            'type' => "Authorization for Recurring Credit Card Charges",
        ], [
            'clickable'      => true,
            'ind'            => $parentIndex++,
            'only_for_admin' => true,
        ]);
        
        PatientDocumentType::updateOrCreate([
            'type' => "Additional Document",
        ], [
            'clickable' => true,
            'ind'       => $parentIndex++,
        ]);
        
        PatientDocumentType::updateOrCreate([
            'type' => "Image / Picture",
        ], [
            'clickable' => true,
            'ind'       => $parentIndex++,
        ]);
        
        //-------------------- [ Tridiuum Types ] --------------------
        PatientDocumentType::updateOrCreate([
            'type' => "Initial Assessment Tridiuum",
        ], [
            'clickable'      => true,
            'ind'            => $parentIndex++,
            'only_for_admin' => true,
            'type_id'        => PatientDocumentType::INITIAL_ASSESSMENT_TYPE,
        ]);
        
        PatientDocumentType::updateOrCreate([
            'type' => "Discharge Summary Tridiuum",
        ], [
            'clickable'      => true,
            'ind'            => $parentIndex++,
            'only_for_admin' => true,
            'type_id'        => PatientDocumentType::DISCHARGE_SUMMARY_TYPE,
        ]);
        
        PatientDocumentType::updateOrCreate([
            'type' => "Other Tridiuum",
        ], [
            'clickable'      => true,
            'ind'            => $parentIndex++,
            'only_for_admin' => true,
        ]);

        PatientDocumentType::updateOrCreate([
            'type' => "Eligibility Verification",
        ], [
            'clickable'      => true,
            'ind'            => $parentIndex++,
            'only_for_admin' => true,
        ]);
    }
}

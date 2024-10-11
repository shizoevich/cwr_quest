<?php

use App\Models\Patient\DocumentRequest\PatientFormType;
use App\PatientDocumentType;
use Illuminate\Database\Seeder;

class PatientFormTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            [
                'document_type_id' => PatientDocumentType::getNewPatientId(),
                'name' => 'new_patient',
                'title' => 'Patient Information / Informed Consent / Privacy Notice',
                'visible_in_modal' => true,
                'visible_in_tab' => true,
                'is_required' => true,
                'patient_can_skip_form' => false,
            ],
            [
                'document_type_id' => PatientDocumentType::getAgreementForServiceId(),
                'name' => 'agreement_for_service_and_hipaa_privacy_notice_and_patient_rights',
                'title' => 'Agreement for Service & HIPAA Privacy Notice & Patient Rights & Notice to Psychotherapy Clients',
                'visible_in_modal' => false,
                'visible_in_tab' => false,
                'is_required' => true,
                'patient_can_skip_form' => false,
            ],
            [
                'document_type_id' => PatientDocumentType::getPaymentForServiceId(),
                'name' => 'payment_for_service',
                'title' => 'Payment for Service and Fee Arrangements',
                'visible_in_modal' => true,
                'visible_in_tab' => true,
                'is_required' => true,
                'patient_can_skip_form' => false,
            ],
            [
                'document_type_id' => null,
                'name' => 'attendance_policy',
                'title' => 'Attendance Policy',
                'visible_in_modal' => false,
                'visible_in_tab' => false,
                'is_required' => true,
                'patient_can_skip_form' => false,
            ],
            [
                'document_type_id' => null,
                'name' => 'credit_card_on_file',
                'title' => 'Add a Credit Card on File',
                'visible_in_modal' => false,
                'visible_in_tab' => true,
                'is_required' => false,
                'patient_can_skip_form' => false,
            ],
            [
                'document_type_id' => PatientDocumentType::getAuthToReleaseId(),
                'name' => 'confidential_information',
                'title' => 'Authorization to Release Confidential Information',
                'visible_in_modal' => true,
                'visible_in_tab' => true,
                'is_required' => true,
                'patient_can_skip_form' => false,
            ],
            [
                'document_type_id' => PatientDocumentType::getTelehealthId(),
                'name' => 'telehealth',
                'title' => 'Telehealth Consent Form',
                'visible_in_modal' => true,
                'visible_in_tab' => true,
                'is_required' => true,
                'patient_can_skip_form' => false,
            ],
            [
                'document_type_id' => null,
                'name' => 'supporting_documents',
                'title' => 'Supporting Documents',
                'visible_in_modal' => true,
                'visible_in_tab' => true,
                'is_required' => true,
                'patient_can_skip_form' => false,
            ],
        ];
        
        foreach ($types as $order => $type) {
            $type['order'] = $order + 1;
            PatientFormType::query()->updateOrCreate(['name' => $type['name']], $type);
        }
    }
}

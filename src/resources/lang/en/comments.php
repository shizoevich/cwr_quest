<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Comments Language Lines
    |--------------------------------------------------------------------------
    |
    */

    'document_sent' => 'The document has been sent by <b class="label-blue bold">:sender</b>',
    'document_sent_by' => 'The document has been sent by :method to <b>:recipient</b> by <b class="label-blue bold">:sender</b>',
    'documents_sent' => 'The documents has been sent to <b class="label-blue bold">:recipient</b> by <b class="label-blue bold">:provider</b> <p>Documents: :documents</p>',
    'document_download_by' => 'The document has been downloaded by <b class="label-blue bold">:recipient</b>',
    'document_status_update' => 'Document status: <b>:status</b>. The recipient <b>:recipient</b>',
    'document_preview_by' => 'The document has been viewed by <b class="label-blue bold">:recipient</b>',
    'document_update_by' => 'The document has been updated by <b class="label-blue bold">:recipient</b>',

    'document_status_changed_by' => 'The document status has been changed to <b class="label-blue bold">:status</b> by <b class="label-blue bold">:name</b>',

    'appointment_deleted_from_office_ally' => 'Appointment scheduled on <b>:apptdate</b> @ <b>:appttime</b> was deleted in OfficeAlly',

    'removal_request_has_been_sent' => '<b class="label-blue bold">:provider_name</b> asked to remove the patient from the list due to the following reason: :reason',
    'removal_request_has_been_approved' => '<b class="label-blue bold">:approved_by</b> removed the patient from the list of <b class="label-blue bold">:provider_name</b>',
    'removal_request_has_been_declined' => '<b class="label-blue bold">:declined_by</b> declined the request to remove the patient from the list of <b class="label-blue bold">:provider_name</b> due to the following reason: :reason',
    'removal_request_has_been_canceled_by_therapist' => '<b class="label-blue bold">:canceled_by</b> has canceled the removal request due to the following reason: :reason',

    'patient_note_unlock_request_has_been_sent' => '<b class="label-blue bold">:provider_name</b> asked to unlock patient note editing (Date of Service: :date_of_service) due to the following reason: :reason',
    'patient_note_unlock_request_has_been_approved' => '<b class="label-blue bold">:approved_by</b> unlocked patient note editing (Date of Service: :date_of_service) for <b class="label-blue bold">:provider_name</b>',
    'patient_note_unlock_request_has_been_declined' => '<b class="label-blue bold">:declined_by</b> declined the request to unlock patient note editing (Date of Service: :date_of_service) for <b class="label-blue bold">:provider_name</b> due to the following reason: :reason',
    'patient_note_unlock_request_has_been_canceled_by_therapist' => '<b class="label-blue bold">:canceled_by</b> has canceled the request to unlock patient note editing (Date of Service: :date_of_service) due to the following reason: :reason',

    'admin_assigned_provider' => '<b class="label-blue bold">:admin_name</b> assigned <b class="label-blue bold">:provider_name</b> to the patient.',
    'admin_unassigned_provider' => '<b class="label-blue bold">:admin_name</b> unassigned  <b class="label-blue bold">:provider_name</b> from the patient.',
    'admin_unassigned_provider_with_reason' => '<b class="label-blue bold">:admin_name</b> unassigned  <b class="label-blue bold">:provider_name</b> from the patient for the following reason: :reason.',

    'provider_assigned_automatically' => '<b class="label-blue bold">:provider_name</b> has been automatically assigned.',
    'provider_unassigned_automatically' => '<b class="label-blue bold">:provider_name</b> has been automatically unassigned.',

    'diagnose_changed_by_provider' => 'ICD Code(s) has been changed by <b class="label-blue bold">:provider_name</b>',

    'diagnose_changed_in_oa' => 'ICD Code(s) has been changed in OfficeAlly',

    'admin_was_archive_patient' => 'Patient status was changed to <span class="patient-status-text">ARCHIVED</span> by <b class="label-blue bold">:user_name</b>',
    'admin_was_archive_patient_with_comment' => 'Patient status was changed to <span class="patient-status-text">ARCHIVED</span> by <b class="label-blue bold">:user_name</b> with the following comment: :comment',
    'admin_was_delete_appointment' => 'Appointment scheduled on <b>:apptdate</b> @ <b>:appttime</b> was deleted in OfficeAlly by <b class="label-blue bold">:user_name</b>',
    'admin_removed_email_from_reject_list' => 'Email <b>:email</b> was removed from blacklist by <b class="label-blue bold">:user_name</b>',

    'patient_inquiry_was_created_by_system' => 'Inquiry was created',
    'patient_inquiry_was_created_by_admin' => 'Inquiry was created by <b>:admin_name</b>',
    'patient_inquiry_patient_was_created_from_patient_lead_by_system' => 'Patient was created in inquiry',
    'patient_inquiry_patient_was_created_from_patient_lead_by_admin' => 'Patient was created in inquiry by <b>:admin_name</b>',
    'patient_inquiry_stage_changed_by_system' => 'Inquiry stage was changed from <b>:old_stage_name</b> to <b>:new_stage_name</b>',
    'patient_inquiry_stage_changed_by_system_due_to_complete' => 'Inquiry stage was changed from <b>:old_stage_name</b> to <b>:new_stage_name</b> due to appointment completion',
    'patient_inquiry_stage_changed_by_system_due_to_cancel' => 'Inquiry stage was changed from <b>:old_stage_name</b> to <b>:new_stage_name</b> due to appointment cancellation',
    'patient_inquiry_stage_changed_by_admin' => 'Inquiry stage was changed from <b>:old_stage_name</b> to <b>:new_stage_name</b> by <b>:admin_name</b> :comment',
    'patient_inquiry_was_archived' => 'Inquiry was archived by <b>:admin_name</b> with the following comment: :comment',
    'patient_inquiry_was_closed' => 'Inquiry was closed',
    'patient_inquiry_was_completed' => 'Inquiry was successfully closed due to a completed appointment',
];
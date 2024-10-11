<?php

return [
    'survey_link' => env('PATIENT_SATISFACTION_SURVEY_LINK', 'https://cwrsite.wpengine.com/feedback-survey/?therapist_name=%s&appointment_date=%s&appointment_id=%s'),
    'api_url' => env('PATIENT_SATISFACTION_SURVEY_API_URL', 'https://cwrsite.wpengine.com/wp-json/frm/v2/'),
    'api_key' => env('PATIENT_SATISFACTION_SURVEY_API_KEY'),
    'form_id' => env('PATIENT_SATISFACTION_SURVEY_FORM_ID'),
];

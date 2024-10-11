<?php
return [
    'uri_lifetime' => (int)env('PATIENT_DOCUMENT_REQUEST_URI_LIFETIME', 30), //days
    'download_uri_lifetime' => (int)env('PATIENT_DOCUMENT_REQUEST_DOWNLOAD_URI_LIFETIME', 14), //days
    'remind_after_send' => (int)env('PATIENT_DOCUMENT_REQUEST_REMIND_AFTER_SEND', 24) // hours
];
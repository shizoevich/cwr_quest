<?php
/**
 * Created by PhpStorm.
 * User: eremenko_aa
 * Date: 08.01.2018
 * Time: 16:12
 */

return [
    'autologout_after' => env('AUTOLOGOUT_AFTER', (60 * 60)), //sec
    'except_routes' => [
        '/^\\/patient\\/[\d]{4,}\\/diagnoses/', // /patient/{id}/diagnoses[?query=]
        '/^\\/patient\\/[\d]{4,}\\/appointment-dates$/', // /patient/{id}/appointment-dates
        '/^\\/patient\\/[\d]{4,}\\/has-initial-assessment$/', // /patient/{id}/has-initial-assessment
        '/^\\/patient\\/upload-file$/', // /patient/upload-file
        '/^\\/appointment\\/get-time-by-date$/', // /appointment/get-time-by-date
        '/^\\/patient\\/[\d]{4,}\\/appointment-document-dates$/', // /patient/{id}/appointment-document-dates
        '/^\\/patient\\/[\d]{4,}\\/diagnoses-codes/', // /patient/{id}/diagnoses-codes[?query=]
    ],
];
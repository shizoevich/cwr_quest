<?php
/**
 * Created by PhpStorm.
 * User: braginec_dv
 * Date: 28.06.2017
 * Time: 14:03
 */
return [
    "parsed_pages_dir" => storage_path() . '/parsed_pages',
    "parsing_depth" => 14,
    "parsing_depth_after_today" => 31,
    "emails" => env('PARSER_ERROR_MAIL_TO', ''),
    'job_retry_backoff_intervals' => [
        900, // 15 minutes
        3600, // 1 hour
        14400, // 4 hour
        43200, // 12 hour
        43200, // 12 hour 
        86400, // 24 hour
        86400, // 24 hour
        86400 // 24 hour
    ],

    "thread_count" => [
        'patient-profile' => 4,
        'patient-alerts' => 4,
        'patient-visits' => 2,
        'provider-profile' => 4,
    ],

    'tridiuum' => [
        'enabled' => env('TRIDIUUM_ENABLED', false),
        'log_table' => env('TRIDIUUM_LOG_TABLE', 'tridiuum_request_logs'),
        'get_patients' => [
            'additional_logging_enabled' => env('TRIDIUUM_GET_PATIENTS_ADDITIONAL_LOGGING_ENABLED', true),
            'log_file_storage_path' => env('TRIDIUUM_GET_PATIENTS_LOG_FILE_STORAGE_PATH', 'logs/get-patients.log'),
        ],
        'get_appointments' => [
            'additional_logging_enabled' => env('TRIDIUUM_GET_APPOINTMENTS_ADDITIONAL_LOGGING_ENABLED', true),
            'log_file_storage_path' => env('TRIDIUUM_GET_APPOINTMENTS_LOG_FILE_STORAGE_PATH', 'logs/get-appointments.log'),
        ],
    ],
    
    'job_limits' => [
        'parser'                => (int) env('PARSER_JOBS_LIMIT', 25),
        'tridiuum_parser'       => (int) env('TRIDIUUM_PARSER_JOBS_LIMIT', 10),
        'tridiuum'              => (int) env('TRIDIUUM_JOBS_LIMIT', 10),
        'tridiuum_availability' => (int) env('TRIDIUUM_AVAILABILITY_JOBS_LIMIT', 30),
    ],
];
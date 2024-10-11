<?php


$params = [
    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        'document_templates' => [
            'driver' => 'local',
            'root' => storage_path('app/public/document_templates'),
            'url' => env('APP_URL').'/storage/document_templates',
            'visibility' => 'private',
        ],

        'temp_pdf' => [
            'driver' => 'local',
            'root' => storage_path('app/temp_pdf'),
        ],
        'temp_patient_forms' => [
            'driver' => 'local',
            'root' => storage_path('app/temp_patient_forms'),
        ],

        'salary_reports' => [
            'driver' => 'local',
            'root' => storage_path('app/salary_reports'),
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],

        'nextcloud' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET_NEXTCLOUD'),
        ],

        'google' => [
            'driver' => 'google',
            'clientId' => env('GOOGLE_DRIVE_CLIENT_ID'),
            'clientSecret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
            'refreshToken' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
            'folderId' => env('GOOGLE_DRIVE_FOLDER_ID'),
        ],
    ],

    'files_storage' => env('FILESYSTEM_FILES')
];

if(env('FILESYSTEM_FILES') === 's3') {
    $params['disks']['photos'] = [
        'driver' => 's3',
        'key' => env('AWS_KEY'),
        'secret' => env('AWS_SECRET'),
        'region' => env('AWS_REGION'),
        'bucket' => env('AWS_BUCKET_PHOTOS'),
    ];
    $params['disks']['therapists_photos'] = [
        'driver' => 's3',
        'key' => env('AWS_KEY'),
        'secret' => env('AWS_SECRET'),
        'region' => env('AWS_REGION'),
        'bucket' => env('AWS_BUCKET_THERAPISTS_PHOTOS'),
    ];
    $params['disks']['therapists_comments_files'] = [
        'driver' => 's3',
        'key' => env('AWS_KEY'),
        'secret' => env('AWS_SECRET'),
        'region' => env('AWS_REGION'),
        'bucket' => env('AWS_BUCKET_THERAPISTS_COMMENTS_FILES'),
    ];
    $params['disks']['signatures'] = [
        'driver' => 's3',
        'key' => env('AWS_KEY'),
        'secret' => env('AWS_SECRET'),
        'region' => env('AWS_REGION'),
        'bucket' => env('AWS_BUCKET_SIGNATURES'),
    ];
    $params['disks']['progress_notes'] = [
        'driver' => 's3',
        'key' => env('AWS_KEY'),
        'secret' => env('AWS_SECRET'),
        'region' => env('AWS_REGION'),
        'bucket' => env('AWS_BUCKET_PROGRESS_NOTES'),
    ];
    $params['disks']['patients_docs'] = [
        'driver' => 's3',
        'key' => env('AWS_KEY'),
        'secret' => env('AWS_SECRET'),
        'region' => env('AWS_REGION'), 
        'bucket' => env('AWS_BUCKET_PATIENTS_DOCS'),
    ];
    $params['disks']['faxes'] = [
        'driver' => 's3',
        'key' => env('AWS_KEY'),
        'secret' => env('AWS_SECRET'),
        'region' => env('AWS_REGION'),
        'bucket' => env('AWS_BUCKET_FAXES'),
    ];
    $params['disks']['patient_assessment_forms'] = [
        'driver' => 's3',
        'key' => env('AWS_KEY'),
        'secret' => env('AWS_SECRET'),
        'region' => env('AWS_REGION'),
        'bucket' => env('AWS_BUCKET_PATIENT_ASSESSMENT_FORMS'),
    ];
    $params['disks']['harassment_certificates'] = [
        'driver' => 's3',
        'key' => env('AWS_KEY'),
        'secret' => env('AWS_SECRET'),
        'region' => env('AWS_REGION'),
        'bucket' => env('AWS_BUCKET_HARASSMENT_CERTIFICATES'),
    ];
    $params['disks']['cancel_fee'] = [
        'driver' => 'local',
        'root' => storage_path('app/public/cancel_fee'),
        'url' => env('APP_URL') . '/storage/cancel_fee',
        'visibility' => 'public',
    ];
    $params['disks']['zip_archive'] = [
        'driver' => 'local',
        'root' => storage_path('app/public/zip_archive'),
        'url' => env('APP_URL') . '/storage/zip_archive',
        'visibility' => 'public',
    ];
} else if(env('FILESYSTEM_FILES') === 'public') {
    $params['disks']['photos'] = [
        'driver' => 'local',
        'root' => storage_path('app/public/photos'),
        'url' => env('APP_URL') . '/storage/photos',
        'visibility' => 'public',
    ];
    $params['disks']['therapists_photos'] = [
        'driver' => 'local',
        'root' => storage_path('app/public/photos'),
        'url' => env('APP_URL') . '/storage/photos',
        'visibility' => 'public',
    ];
    $params['disks']['therapists_comments_files'] = [
        'driver' => 'local',
        'root' => storage_path('app/public/therapists-comments-files'),
        'url' => env('APP_URL') . '/storage/therapists-comments-files',
        'visibility' => 'public',
    ];
    $params['disks']['signatures'] = [
        'driver' => 'local',
        'root' => storage_path('app/public/signatures'),
        'url' => env('APP_URL') . '/storage/signatures',
        'visibility' => 'public',
    ];
    $params['disks']['progress_notes'] = [
        'driver' => 'local',
        'root' => storage_path('app/public/progress_notes'),
        'url' => env('APP_URL') . '/storage/progress_notes',
        'visibility' => 'public',
    ];
    $params['disks']['patients_docs'] = [
        'driver' => 'local',
        'root' => storage_path('app/public/patients_docs'),
        'url' => env('APP_URL') . '/storage/patients_docs',
        'visibility' => 'public',
    ];
    $params['disks']['faxes'] = [
        'driver' => 'local',
        'root' => storage_path('app/public/faxes'),
        'url' => env('APP_URL') . '/storage/faxes',
        'visibility' => 'public',
    ];
    $params['disks']['patient_assessment_forms'] = [
        'driver' => 'local',
        'root' => storage_path('app/public/patient_assessment_forms'),
        'url' => env('APP_URL') . '/storage/patient_assessment_forms',
        'visibility' => 'public',
    ];
    $params['disks']['cancel_fee'] = [
        'driver' => 'local',
        'root' => storage_path('app/public/cancel_fee'),
        'url' => env('APP_URL') . '/storage/cancel_fee',
        'visibility' => 'public',
    ];
    $params['disks']['zip_archive'] = [
        'driver' => 'local',
        'root' => storage_path('app/public/zip_archive'),
        'url' => env('APP_URL') . '/storage/zip_archive',
        'visibility' => 'public',
    ];
    $params['disks']['harassment_certificates'] = [
        'driver' => 'local',
        'root' => storage_path('app/public/harassment_certificates'),
        'url' => env('APP_URL') . '/storage/harassment_certificates',
        'visibility' => 'public',
    ];
} else {
    $params['disks']['photos'] = [
        'driver' => 'local',
        'root' => storage_path('app/photos'),
    ];
    $params['disks']['therapists_photos'] = [
        'driver' => 'local',
        'root' => storage_path('app/photos'),
    ];
    $params['disks']['therapists_comments_files'] = [
        'driver' => 'local',
        'root' => storage_path('app/therapists-comments-files'),
    ];
    $params['disks']['signatures'] = [
        'driver' => 'local',
        'root' => storage_path('app/signatures'),
    ];
    $params['disks']['progress_notes'] = [
        'driver' => 'local',
        'root' => storage_path('app/progress_notes'),
    ];
    $params['disks']['patients_docs'] = [
        'driver' => 'local',
        'root' => storage_path('app/patients_docs'),
    ];
    $params['disks']['faxes'] = [
        'driver' => 'local',
        'root' => storage_path('app/faxes'),
    ];
    $params['disks']['patient_assessment_forms'] = [
        'driver' => 'local',
        'root' => storage_path('app/patient_assessment_forms'),
    ];
    $params['disks']['cancel_fee'] = [
        'driver' => 'local',
        'root' => storage_path('app/publi/cancel_fee'),
    ];
    $params['disks']['zip_archive'] = [
        'driver' => 'local',
        'root' => storage_path('app/public/zip_archive'),
    ];
    $params['disks']['harassment_certificates'] = [
        'driver' => 'local',
        'root' => storage_path('app/public/harassment_certificates'),
    ];
}

return $params;


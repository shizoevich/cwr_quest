<?php

return [
    'supported_formats' => [
        'pdf',
        'psd',
        'doc',
        'docx',
        'dot',
        'mcw',
        'xls',
        'xlsx',
        'ppt',
        'pptx',
        'vsd',
        'vdx',
        'pub',
        'wps',
        'wri',
        'awd',
        'tif',
        'tiff',
        'gif',
        'jpg',
        'jpeg',
        'bmp',
        'png',
        'pcx',
        'tga',
        'rtf',
        'txt',
        'log',
        'h',
        'cpp',
        'c',
        'err',
        'hpp',
        'wk1',
        'wk3',
        'wk4',
        'wq1',
        'xml',
        'html',
        'htm',
        'csv'
    ],
    'max_file_size'     => 19000000,

    'jwt' => env('RC_JWT'),

    'faxes'     => [
        'jwt' => env('RC_JWT_FAXES'),
    ],

    'sms'     => [
        'jwt' => env('RC_JWT_SMS'),
    ],

    'sms_from' => env('RC_SMS_FROM'),

    'appKey'    => env('RC_API_KEY'),
    'appSecret' => env('RC_API_SECRET'),
    'server'    => env('RC_SERVER','https://platform.ringcentral.com'),
    'webhook_address'   => env('RC_WEBHOOK_ADDRESS'),
    'webhook_verification_token'   => env('RC_WEBHOOK_VERIFICATION_TOKEN'),
];
<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'dashboard/doctors',
        'dashboard/invite',
        'patient/save-note',
        'patient/quick-save-note',
        'logout',
        'patient/update-note',
        'patient/upload-file',
        'mandrill-webhooks',
        'ringcentral-webhook',
        'forms/patient/upload-photo',
        'api/*',
    ];
}

<?php

namespace App\Http;

use App\Http\Middleware\ProfileCompleted;
use App\Http\Middleware\RedirectIfHasReauthorizationRequests;
use App\Http\Middleware\RedirectIfHasUnresolvedPastAppointments;
use App\Http\Middleware\RedirectIfTimesheetIsNotCompleted;
use App\Http\Middleware\RedirectIfHasUnresolvedNotifications;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \App\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
//            \App\Http\Middleware\LastRequestTime::class,
        ],
        
        'web_api' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
        ],

        'user-provider' => [
            \App\Http\Middleware\AutoLogout::class,
            'auth',
            \App\Http\Middleware\UserProvider::class,
        ],

        'admin' => [
            \App\Http\Middleware\AutoLogout::class,
            'auth',
            \App\Http\Middleware\Admin::class,
        ],

        'admin-secretary' => [
            \App\Http\Middleware\AutoLogout::class,
            'auth',
            \App\Http\Middleware\AdminSecretary::class,
        ],

        'admin-or-supervisor' => [
            \App\Http\Middleware\AdminOrSupervisor::class,
        ],

        'only-admin' => [
            \App\Http\Middleware\AutoLogout::class,
            'auth',
            \App\Http\Middleware\OnlyAdmin::class,
        ],

        'system' => [
            'admin',
            \App\Http\Middleware\System::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'                                     => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic'                               => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings'                                 => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can'                                      => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'                                    => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle'                                 => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'clear-string'                             => \App\Http\Middleware\ClearString::class,
        'profile-completed'                        => ProfileCompleted::class,
        'redirect-if-has-unresolved-appointments'  => RedirectIfHasUnresolvedPastAppointments::class,
        'redirect-if-timesheet-is-not-completed'   => RedirectIfTimesheetIsNotCompleted::class,
        'redirect-if-has-unresolved-notifications' => RedirectIfHasUnresolvedNotifications::class,
        'redirect-if-has-reauthorization-requests' => RedirectIfHasReauthorizationRequests::class,
    ];
}

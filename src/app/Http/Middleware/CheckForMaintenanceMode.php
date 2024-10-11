<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode as ParentCheckForMaintenanceMode;

class CheckForMaintenanceMode extends ParentCheckForMaintenanceMode
{
    public function handle($request, Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch(MaintenanceModeException $e) {
            $allowedIps = config('app.maintenance_allowed_ips');
            if(\in_array($request->ip(), $allowedIps)) {
                return $next($request);
            }
            throw $e;
        }
    }
}

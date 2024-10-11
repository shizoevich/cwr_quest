<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class UserProvider {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $user = Auth::user();
        if(!$user->isAdmin() && !$user->isInsuranceAudit()) {
            if(!$user->isProviderAttached() || empty($user->meta->signature) || !$user->hasRole('provider')) {
                return redirect()->route('registration-complete');
            }
        }

        return $next($request);
    }
}

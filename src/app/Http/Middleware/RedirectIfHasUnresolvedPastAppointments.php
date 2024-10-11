<?php

namespace App\Http\Middleware;

use Closure;

class RedirectIfHasUnresolvedPastAppointments
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(config('app.redirect_if_has_unresolved_past_appointments') && session('has_unresolved_past_appointments')) {
            return redirect()->route('past-appointments');
        }
        
        return $next($request);
    }
}

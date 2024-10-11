<?php

namespace App\Http\Middleware;

use Closure;

class RedirectIfHasReauthorizationRequests
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
        if(config('app.redirect_if_has_reauthorization_requests') && session('has_reauthorization_requests')) {
            session(['has_reauthorization_requests' => false]);
            return redirect()->route('reauthorization-requests', ['is_only_overdue' => 'true']);
        }
        
        return $next($request);
    }
}

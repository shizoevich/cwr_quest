<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class OnlyAdmin {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if(!Auth::user()->isOnlyAdmin()) {
            if($request->expectsJson()) {
                abort(403);
            }

            return back();
        }

        return $next($request);
    }
}

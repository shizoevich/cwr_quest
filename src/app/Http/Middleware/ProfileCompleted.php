<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ProfileCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && null === Auth::user()->profile_completed_at) {
            if($request->expectsJson()) {
                abort(403);
            }
            return redirect(route('profile.index') . '?redirect=' . route('home'));
        }

        return $next($request);
    }
}

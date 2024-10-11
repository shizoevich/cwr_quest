<?php

namespace App\Http\Middleware;

use App\Option;
use Closure;

class System {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if(!$request->has('password') || !\Hash::check($request->get('password'), Option::getOptionValue('system_password'))) {
            abort(403);
        }

        return $next($request);
    }
}

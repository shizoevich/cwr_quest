<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;

class AutoLogout {

    /**
     * session lifetime (in seconds)
     * @var int
     */
    private $sessionLifetime;

    private $exceptedRoutes;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $response = $next($request);
        $this->sessionLifetime = intval(config('autologout.autologout_after'));
        $this->exceptedRoutes = config('autologout.except_routes');
        $requestUri = $request->getRequestUri();
        foreach($this->exceptedRoutes as $pattern) {
            if(preg_match($pattern, $requestUri)) {
                return $response;
            }
        }

        if (Auth::check()) {
            $lastRequestTime = session('last_request_time');
            $now = Carbon::now();
            if (!empty($lastRequestTime)) {
                $lastRequestTime = Carbon::createFromTimestamp($lastRequestTime);
                $diffInSeconds = $lastRequestTime->diffInSeconds($now, false);
                if($diffInSeconds >= $this->sessionLifetime) {
                    $responseStatusCode = $response->status();
                    if($responseStatusCode >= 200 && $responseStatusCode < 400) {
                        Auth::logout();
                        session(['last_request_time' => null]);
                        if ($request->expectsJson()) {
                            return response()->json(['error' => 'Unauthenticated.'], 401);
                        }

                        return response()->redirectTo($requestUri);
                    }
                }

            }
            session(['last_request_time' => $now->timestamp]);
        } else {
            session(['last_request_time' => null]);
        }

        return $response;
    }
}

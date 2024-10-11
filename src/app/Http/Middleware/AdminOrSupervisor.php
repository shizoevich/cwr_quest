<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Http\Request;

class AdminOrSupervisor
{
    /**
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var User $user */
        $user = auth()->user();
        
        if (!$user) {
            return back();
        }

        if ($user->isSupervisor() || $user->isAdmin() || $user->isSecretary()) {
            return $next($request);
        }

        return redirect()->route('vue-chart');
    }
}
<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSecretary
{
    /**
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var User $user */
        $user = auth()->user();

        if($user->isAdmin() || $user->isSecretary()) {
            return $next($request);
        }

        if(!$user->isAdmin() && !$user->isInsuranceAudit()) {
            if(!$user->isProviderAttached() || empty($user->meta->signature) || !$user->hasRole('provider')) {
                return redirect()->route('registration-complete');
            }
        }

        return redirect()->route('vue-chart');
    }
}


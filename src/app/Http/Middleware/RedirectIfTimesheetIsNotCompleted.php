<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Provider\ProviderRepositoryInterface;
use App\Repositories\Provider\Salary\Timesheet\TimesheetRepositoryInterface;

class RedirectIfTimesheetIsNotCompleted
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
        if (config('app.redirect_if_timesheet_is_not_completed')) {
            $user = Auth::user();
            if (!$user->isAdmin() && !$user->isInsuranceAudit() && $user->provider_id && $user->profile_completed_at && !$user->provider->is_new) {
                $providerRepository = app()->make(ProviderRepositoryInterface::class);
                $timesheetRepository = app()->make(TimesheetRepositoryInterface::class);
                $isBiWeeklyType = $providerRepository->isBiWeeklyType($user->provider);
                if ($isBiWeeklyType && $timesheetRepository->needsRedirect($user->provider)) {
                    return redirect()->route('provider-timesheet', ['force_redirected' => 1, 'step' => 'visits']);
                }
            }
        }
        
        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Repositories\UpdateNotification\UpdateNotificationRepositoryInterface;

class RedirectIfHasUnresolvedNotifications
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
        if(config('app.redirect_if_has_unresolved_notifications')) {
            $user = Auth::user();
            if(!$user->isOnlyAdmin()) {
                $notificationRepository = app()->make(UpdateNotificationRepositoryInterface::class);
                if($notificationRepository->hasUnresolvedNotifications($user)) {
                    return redirect()->route('update-notifications.history');
                }
            }
        }
        
        return $next($request);
    }
}

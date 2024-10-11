<?php

namespace App\Repositories\UpdateNotification;

use Illuminate\Http\Request;
use App\Repositories\UpdateNotification\UpdateNotificationRepositoryInterface;
use App\Http\Requests\UpdateNotification\IndexApi as IndexApiRequest;
use App\Http\Requests\UpdateNotification\Store as StoreRequest;
use App\Http\Requests\UpdateNotification\Show as ShowRequest;
use App\Http\Requests\UpdateNotification\Update as UpdateRequest;
use App\Http\Requests\UpdateNotification\Destroy as DestroyRequest;
use App\Http\Requests\UpdateNotification\HistoryApi as HistoryApiRequest;
use App\Http\Requests\UpdateNotification\AvailableList as AvailableListRequest;
use App\Http\Requests\UpdateNotification\MarkAsOpened as MarkAsOpenedRequest;
use App\Http\Requests\UpdateNotification\MarkAsViewed as MarkAsViewedRequest;
use App\Http\Requests\UpdateNotification\ViewedListApi as ViewedListApiRequest;
use App\Models\UpdateNotification;
use App\Models\UpdateNotificationTemplate;
use App\Role;
use App\User;

use Illuminate\Support\Collection;
use Carbon\Carbon;

class UpdateNotificationRepository implements UpdateNotificationRepositoryInterface
{
    public function all(IndexApiRequest $request): Collection
    {
        return UpdateNotification::all();
    }

    public function getCreateData(Request $request): array
    {
        $allowedRoles = ['provider', 'secretary'];
        $allowedRoleIds = Role::whereIn('role', $allowedRoles)
            ->get()
            ->pluck('id')
            ->toArray();

        $users = User::with('roles')
            ->get()
            ->filter(function ($user) use(&$allowedRoleIds) {
                return !count($user->roles) || $user->checkAllowedRoles($allowedRoleIds);
            })
            ->sortBy(function ($user) {
                return isset($user->name) ? $user->name . ' (' . $user->email . ')' : $user->email;
            });

        $templates = UpdateNotificationTemplate::select('id', 'name')
            ->get();
        
        $user = $request->filled('user_id') ? User::find($request->user_id) : null;
        $template = $request->filled('template_id') ? UpdateNotificationTemplate::find($request->template_id) : null;

        return [
            'users' => $users,
            'templates' => $templates,
            'user' => $user,
            'template' => $template,
        ];
    }

    public function create(StoreRequest $request): UpdateNotification
    {
        $notification = UpdateNotification::create([
            'show_date' => isset($request->show_date) ? Carbon::parse($request->show_date)->format('Y-m-d H:i:00') : Carbon::now()->format('Y-m-d H:i:00'),
            'is_required' => $request->is_required ?? 0,
            'title' => $request->title,
            'content' => $request->content,
        ]);

        foreach ($request->user_ids as $id) {
            $notification->users()->attach($id);
        }

        return $notification;
    }

    public function update(UpdateRequest $request, UpdateNotification $notification): UpdateNotification
    {
        $notification->update([
            'show_date' => isset($request->show_date) ? Carbon::parse($request->show_date)->format('Y-m-d H:i:00') : $notification->show_date,
            'is_required' => $request->is_required ?? 0,
            'title' => $request->title,
            'content' => $request->content,
        ]);

        $currUserIds = $request->user_ids;
        $prevUserIds = $notification
            ->users()
            ->get(['users.id'])
            ->pluck('id')
            ->toArray();
        
        $newUserIds = array_diff($currUserIds, $prevUserIds);
        foreach ($newUserIds as $id) {
            $notification->users()->attach($id);
        }

        $outdatedUserIds = array_diff($prevUserIds, $currUserIds);
        foreach ($outdatedUserIds as $id) {
            $notification->users()->detach($id);
        }

        return $notification;
    }

    public function delete(DestroyRequest $request, UpdateNotification $notification): UpdateNotification
    {
        $notification->delete();

        return $notification;
    }

    public function history(HistoryApiRequest $request): Collection
    {
        $user = \Auth::user();

        $notifications = UpdateNotification::selectRaw('`update_notifications`.*, `update_notification_user`.`viewed_at`')
            ->join('update_notification_user', 'update_notifications.id', '=', 'update_notification_user.update_notification_id')
            ->whereRaw("`update_notifications`.`show_date` <= '" . Carbon::now() . "'")
            ->where('update_notification_user.user_id', '=', $user->id)
            ->get();
        
        $substitutionsData = $this->getUserSubstitutionsData($user);
        return $notifications->map(function ($notification) use (&$substitutionsData, &$user) {
            $notification->content = $this->replaceSubstitutions($notification->content, $substitutionsData);
            $notification->userName = $user->getGeneralFullname();

            return $notification;
        });
    }

    public function availableList(AvailableListRequest $request): Collection
    {
        $user = \Auth::user();

        $notifications = UpdateNotification::selectRaw('`update_notifications`.*')
            ->join('update_notification_user', 'update_notifications.id', '=', 'update_notification_user.update_notification_id')
            ->whereRaw("`update_notifications`.`show_date` <= '" . Carbon::now() . "'")
            ->where('update_notification_user.user_id', '=', $user->id)
            ->whereNull('update_notification_user.viewed_at')
            ->whereRaw("(`update_notification_user`.`remind_after` IS NULL OR `update_notification_user`.`remind_after` < '" . Carbon::now() . "')")
            ->get();
        
        $substitutionsData = $this->getUserSubstitutionsData($user);
        return $notifications->map(function ($notification) use (&$substitutionsData, &$user) {
            $notification->content = $this->replaceSubstitutions($notification->content, $substitutionsData);
            $notification->userName = $user->getGeneralFullname();

            return $notification;
        });
    }

    public function markAsOpened(MarkAsOpenedRequest $request, UpdateNotification $notification): UpdateNotification
    {
        $user = \Auth::user();
        $notification->users()->updateExistingPivot($user->id, [
            'opened_at' => Carbon::now()
        ]);

        return $notification;
    }

    public function markAsViewed(MarkAsViewedRequest $request, UpdateNotification $notification): UpdateNotification
    {
        $user = \Auth::user();
        $notification->users()->updateExistingPivot($user->id, [
            'viewed_at' => Carbon::now()
        ]);

        return $notification;
    }

    public function viewedList(ViewedListApiRequest $request, UpdateNotification $notification): Collection
    {
        $items = $notification
            ->users()
            ->whereNotNull('update_notification_user.viewed_at')
            ->get()
            ->map(function ($user) {
                $user->name = $user->name;
                $user->opened_at = $user->pivot->opened_at;
                $user->viewed_at = $user->pivot->viewed_at;
                return $user;
            });

        return $items;
    }

    public function remindLater(UpdateNotification $notification): UpdateNotification
    {
        $user = \Auth::user();
        $notification->users()->updateExistingPivot($user->id, [
            'remind_after' => Carbon::now()->addMinutes(config('notification.remind_later_gap'))
        ]);

        return $notification;
    }

    public function userNotifications(User $user): Collection
    {
        return UpdateNotification::selectRaw('`update_notifications`.*, `update_notification_user`.`opened_at`, `update_notification_user`.`viewed_at`')
            ->join('update_notification_user', 'update_notifications.id', '=', 'update_notification_user.update_notification_id')
            ->where('update_notification_user.user_id', '=', $user->id)
            ->get();
    }

    public function hasUnresolvedNotifications(User $user): bool
    {
        return UpdateNotification::query()
            ->join('update_notification_user', 'update_notifications.id', '=', 'update_notification_user.update_notification_id')
            ->whereRaw("`update_notifications`.`show_date` <= '" . Carbon::now() . "'")
            ->where('update_notifications.is_required', '=', 1)
            ->where('update_notification_user.user_id', '=', $user->id)
            ->whereNull('update_notification_user.viewed_at')
            ->exists();
    }

    public function getUserSubstitutionsData(User $user): array
    {
        return [
            'full_name' => $user->getFullname() ?? $user->getTherapistFullname(),
            'email' => $user->email,
        ];
    }

    public function replaceSubstitutions(string $text, array $data): string
    {
        $newText = $text;
        foreach ($data as $key => $value) {
            $pattern = $this->getSubstitutionPattern($key);
            $newText = preg_replace($pattern, $value, $newText);
        }

        return $newText;
    }

    public function getSubstitutionPattern(string $key): string
    {
        return '/<span class="\w*?\s?substitution\s?\w*?">' . $key . '<\/span>/';
    }
}

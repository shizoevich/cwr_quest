<?php

namespace App\Repositories\UpdateNotification;

use Illuminate\Http\Request;
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
use App\Role;
use App\User;

use Illuminate\Support\Collection;
use Carbon\Carbon;

interface UpdateNotificationRepositoryInterface
{
    public function all(IndexApiRequest $request): Collection;

    public function getCreateData(Request $request): array;

    public function create(StoreRequest $request): UpdateNotification;

    public function update(UpdateRequest $request, UpdateNotification $notification): UpdateNotification;

    public function delete(DestroyRequest $request, UpdateNotification $notification): UpdateNotification;

    public function history(HistoryApiRequest $request): Collection;

    public function availableList(AvailableListRequest $request): Collection;

    public function markAsOpened(MarkAsOpenedRequest $request, UpdateNotification $notification): UpdateNotification;

    public function markAsViewed(MarkAsViewedRequest $request, UpdateNotification $notification): UpdateNotification;

    public function viewedList(ViewedListApiRequest $request, UpdateNotification $notification): Collection;

    public function remindLater(UpdateNotification $notification): UpdateNotification;

    public function userNotifications(User $user): Collection;

    public function hasUnresolvedNotifications(User $user): bool;

    public function getUserSubstitutionsData(User $user): array;

    public function replaceSubstitutions(string $text, array $data): string;

    public function getSubstitutionPattern(string $key): string;
}

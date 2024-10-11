<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
use App\Role;
use App\User;

use Carbon\Carbon;

class UpdateNotificationController extends Controller
{
    protected $notificationRepository;

    public function __construct(UpdateNotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function index()
    {
        return view('update-notifications.index');
    }

    public function indexApi(IndexApiRequest $request)
    {
        $notifications = $this->notificationRepository->all($request);
        
        return response()->json($notifications);
    }

    public function create(Request $request)
    {
        $data = $this->notificationRepository->getCreateData($request);

        return view('update-notifications.form', $data);
    }

    public function store(StoreRequest $request)
    {
        $this->notificationRepository->create($request);

        return redirect()->route('update-notifications.index');
    }

    public function show(ShowRequest $request, UpdateNotification $notification)
    {
        $notification->users = $notification->users;

        return response()->json([
            'notification' => $notification
        ]);
    }

    public function edit(Request $request, UpdateNotification $notification)
    {
        $notification->users = $notification->users;

        $data = array_merge($this->notificationRepository->getCreateData($request), [
            'notification' => $notification
        ]);

        return view('update-notifications.form', $data);
    }

    public function update(UpdateRequest $request, UpdateNotification $notification)
    {
        $this->notificationRepository->update($request, $notification);

        return redirect()->route('update-notifications.index');
    }

    public function destroy(DestroyRequest $request, UpdateNotification $notification)
    {
        $this->notificationRepository->delete($request, $notification);

        return response()->json([
            'success' => true
        ]);
    }

    public function history()
    {
        return view('update-notifications.history');
    }

    public function historyApi(HistoryApiRequest $request)
    {
        $notifications = $this->notificationRepository->history($request);
        
        return response()->json($notifications);
    }

    public function availableList(AvailableListRequest $request)
    {
        $notifications = $this->notificationRepository->availableList($request);
        
        return response()->json($notifications);
    }

    public function markAsOpened(MarkAsOpenedRequest $request, UpdateNotification $notification)
    {
        $this->notificationRepository->markAsOpened($request, $notification);

        return response()->json([
            'success' => true
        ]);
    }

    public function markAsViewed(MarkAsViewedRequest $request, UpdateNotification $notification)
    {
        $this->notificationRepository->markAsViewed($request, $notification);

        return response()->json([
            'success' => true
        ]);
    }

    public function viewedList()
    {
        return view('update-notifications.viewed-list');
    }

    public function viewedListApi(ViewedListApiRequest $request, UpdateNotification $notification)
    {
        $items = $this->notificationRepository->viewedList($request, $notification);

        return response()->json($items);
    }

    public function remindLater(UpdateNotification $notification)
    {
        $this->notificationRepository->remindLater($notification);

        return response()->json([
            'success' => true
        ]);
    }

    public function userNotifications()
    {
        return view('update-notifications.user-notifications');
    }

    public function userNotificationsApi(User $user)
    {
        $notifications = $this->notificationRepository->userNotifications($user);
        
        return response()->json($notifications);
    }
}

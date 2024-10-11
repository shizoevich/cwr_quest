<?php

namespace App\Repositories\UpdateNotificationTemplate;

use App\Repositories\UpdateNotificationTemplate\UpdateNotificationTemplateRepositoryInterface;
use App\Repositories\UpdateNotification\UpdateNotificationRepositoryInterface;
use App\Http\Requests\UpdateNotificationTemplate\Store as StoreRequest;
use App\Http\Requests\UpdateNotificationTemplate\Update as UpdateRequest;
use App\Models\UpdateNotificationTemplate;

use Illuminate\Support\Collection;
use Carbon\Carbon;

class UpdateNotificationTemplateRepository implements UpdateNotificationTemplateRepositoryInterface
{
    public function all(): Collection
    {
        return UpdateNotificationTemplate::all();
    }

    public function create(StoreRequest $request): UpdateNotificationTemplate
    {
        $template = UpdateNotificationTemplate::create([
            'name' => $request->name,
            'notification_title' => $request->notification_title,
            'notification_content' => $request->notification_content,
        ]);

        return $template;
    }

    public function update(UpdateRequest $request, UpdateNotificationTemplate $template): UpdateNotificationTemplate
    {
        $template->update([
            'name' => $request->name,
            'notification_title' => $request->notification_title,
            'notification_content' => $request->notification_content,
        ]);

        return $template;
    }

    public function delete(UpdateNotificationTemplate $template): UpdateNotificationTemplate
    {
        $template->delete();

        return $template;
    }
}

<?php

namespace App\Repositories\UpdateNotificationTemplate;

use App\Http\Requests\UpdateNotificationTemplate\Store as StoreRequest;
use App\Http\Requests\UpdateNotificationTemplate\Update as UpdateRequest;
use App\Models\UpdateNotificationTemplate;

use Illuminate\Support\Collection;
use Carbon\Carbon;

interface UpdateNotificationTemplateRepositoryInterface
{
    public function all(): Collection;

    public function create(StoreRequest $request): UpdateNotificationTemplate;

    public function update(UpdateRequest $request, UpdateNotificationTemplate $template): UpdateNotificationTemplate;

    public function delete(UpdateNotificationTemplate $template): UpdateNotificationTemplate;
}

<?php

namespace App\Repositories\UpdateNotificationSubstitution;

use App\Repositories\UpdateNotificationSubstitution\UpdateNotificationSubstitutionRepositoryInterface;
use App\Models\UpdateNotificationSubstitution;

use Illuminate\Support\Collection;

class UpdateNotificationSubstitutionRepository implements UpdateNotificationSubstitutionRepositoryInterface
{
    public function all(): Collection
    {
        return UpdateNotificationSubstitution::all();
    }
}

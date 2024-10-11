<?php

namespace App\Repositories\UpdateNotificationSubstitution;

use App\Models\UpdateNotificationSubstitution;

use Illuminate\Support\Collection;

interface UpdateNotificationSubstitutionRepositoryInterface
{
    public function all(): Collection;
}

<?php

namespace App\Repositories\OfficeRoom;

use App\Office;
use Illuminate\Support\Collection;

interface OfficeRoomRepositoryInterface
{
    /**
     * @param array $data
     * @return Collection
     */
    public function getAllFree(array $data): Collection;
}
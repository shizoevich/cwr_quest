<?php

namespace App\Repositories\Office;

use App\Office;
use Illuminate\Database\Eloquent\Collection;

class OfficeRepository implements OfficeRepositoryInterface
{
    /**
     * @return Collection
     */
    public function all(): Collection
    {
        return Office::query()
            ->select([
                'id', 'office'
            ])
            ->orderBy('office')
            ->get();
    }
}
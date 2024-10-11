<?php

namespace App\Repositories\Office;

use Illuminate\Database\Eloquent\Collection;

interface OfficeRepositoryInterface
{
    /**
     * @return Collection
     */
    public function all():Collection;
}
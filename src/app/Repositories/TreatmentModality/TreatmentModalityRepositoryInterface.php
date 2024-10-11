<?php

namespace App\Repositories\TreatmentModality;

use Illuminate\Support\Collection;

interface TreatmentModalityRepositoryInterface
{
    public function getAll(): Collection;
}
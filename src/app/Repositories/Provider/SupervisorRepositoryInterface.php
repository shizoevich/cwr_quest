<?php

namespace App\Repositories\Provider;

use App\Provider;
use Illuminate\Database\Eloquent\Collection;

interface SupervisorRepositoryInterface
{
    public function getSupervisors(): Collection;

    public function getSupervisees(int $supervisorId): Collection;

    public function storeIsSupervisor(bool $isSupervisor, Provider $provider): void;

    public function checkSuperviseeLimit(int $supervisorId): bool;

    public function attachSupervisor(array $data, Provider $provider): void;
}
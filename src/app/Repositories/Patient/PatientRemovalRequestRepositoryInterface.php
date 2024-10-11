<?php

namespace App\Repositories\Patient;

use App\Models\Patient\PatientRemovalRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PatientRemovalRequestRepositoryInterface
{
    public function getList(array $data): array;

    public function getNewRequests(): Collection;

    public function getCheckedRequests(): LengthAwarePaginator;

    public function getActiveRequestsForPatient(int $patientId): Collection;

    public function send(array $data): PatientRemovalRequest;

    public function accept(array $data): void;

    public function decline(array $data): void;

    public function cancel(array $data): void;

}
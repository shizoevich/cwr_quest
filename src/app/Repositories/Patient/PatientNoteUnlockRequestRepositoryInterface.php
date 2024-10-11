<?php

namespace App\Repositories\Patient;

use App\Models\Patient\PatientNoteUnlockRequest;
use App\PatientNote;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PatientNoteUnlockRequestRepositoryInterface
{
    public function getList(array $data): array;

    public function getNewRequests(): Collection;

    public function getCheckedRequests(): LengthAwarePaginator;

    public function getActiveRequests(int $patientNoteId): Collection;

    public function send(array $data, PatientNote $patientNote): PatientNoteUnlockRequest;

    public function accept(array $data): void;

    public function decline(array $data): void;

    public function cancel(array $data): void;
}
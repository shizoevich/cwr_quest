<?php

namespace App\Repositories\ReauthorizationRequestDashboard;

use App\Models\FutureInsuranceReauthorizationData;
use App\Models\SubmittedReauthorizationRequestForm;
use App\Models\SubmittedReauthorizationRequestFormLog;
use Illuminate\Database\Eloquent\Collection;

interface ReauthorizationRequestDashboardRepositoryInterface
{
    public function getUpcomingReauthorizationRequests(array $filters): array;

    public function getSubmittedReauthorizationRequestForms(array $filters): array;

    public function getStages(): Collection;

    public function changeStage(SubmittedReauthorizationRequestForm $form, array $data): SubmittedReauthorizationRequestForm;

    public function createReauthorizationRequestFormWithoutDocument(int $patientId): SubmittedReauthorizationRequestForm;

    public function createLog(SubmittedReauthorizationRequestForm $form, array $data): SubmittedReauthorizationRequestFormLog;

    public function saveFutureInsuranceReauthorizationData(SubmittedReauthorizationRequestForm $form, array $data): FutureInsuranceReauthorizationData;

    public function loadReauthorizationRequestDocument(Collection $patients): void;
}

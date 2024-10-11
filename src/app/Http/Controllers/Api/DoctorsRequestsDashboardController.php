<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DoctorsRequestsDashboard\GetPatientNoteUnlockRequestsListRequest;
use App\Http\Requests\Api\DoctorsRequestsDashboard\GetPatientRemovalRequestsListRequest;
use App\Repositories\Patient\PatientNoteUnlockRequestRepositoryInterface;
use App\Repositories\Patient\PatientRemovalRequestRepositoryInterface;
use Illuminate\Http\JsonResponse;

class DoctorsRequestsDashboardController extends Controller
{
    protected $patientRemovalRequestRepository;
    protected $patientNoteUnlockRequestRepository;

    public function __construct(
        PatientRemovalRequestRepositoryInterface $patientRemovalRequestRepository,
        PatientNoteUnlockRequestRepositoryInterface $patientNoteUnlockRequestRepository
    ) {
        $this->patientRemovalRequestRepository = $patientRemovalRequestRepository;
        $this->patientNoteUnlockRequestRepository = $patientNoteUnlockRequestRepository;
    }

    public function getPatientRemovalRequestsList(GetPatientRemovalRequestsListRequest $request): JsonResponse
    {
        return response()->json($this->patientRemovalRequestRepository->getList($request->validated()));
    }

    public function getPatientNoteUnlockRequestsList(GetPatientNoteUnlockRequestsListRequest $request): JsonResponse
    {
        return response()->json($this->patientNoteUnlockRequestRepository->getList($request->validated()));
    }
}
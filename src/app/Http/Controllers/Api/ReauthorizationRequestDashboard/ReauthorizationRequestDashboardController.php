<?php

namespace App\Http\Controllers\Api\ReauthorizationRequestDashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ReauthorizationRequestDashboard\ChangeStageRequest;
use App\Http\Requests\Api\ReauthorizationRequestDashboard\CreateRequthorizationRequestFormRequest;
use App\Http\Requests\Api\ReauthorizationRequestDashboard\GetSubmittedReauthorizationRequestFormsRequest;
use App\Http\Requests\Api\ReauthorizationRequestDashboard\GetUpcomingReauthorizationRequestsRequest;
use App\Http\Requests\Api\ReauthorizationRequestDashboard\CreateLogRequest;
use App\Http\Requests\Api\ReauthorizationRequestDashboard\SaveFutureInsuranceReauthorizationDataRequest;
use App\Http\Requests\Api\ReauthorizationRequestDashboard\UpdateAuthNumberRequest;
use App\Models\SubmittedReauthorizationRequestForm;
use App\Models\SubmittedReauthorizationRequestFormStage;
use App\PatientInsurancePlan;
use App\Repositories\ReauthorizationRequestDashboard\ReauthorizationRequestDashboardRepositoryInterface;
use App\Repositories\Patient\PatientRepositoryInterface;
use Illuminate\Http\JsonResponse;

class ReauthorizationRequestDashboardController extends Controller
{
    /**
     * @var ReauthorizationRequestDashboardRepositoryInterface
     */
    protected $reauthorizationRequestDashboardRepository;

    /**
     * @var PatientRepositoryInterface
     */
    protected $patientRepository;

    public function __construct(ReauthorizationRequestDashboardRepositoryInterface $reauthorizationRequestDashboardRepository, PatientRepositoryInterface $patientRepository)
    {
        $this->reauthorizationRequestDashboardRepository = $reauthorizationRequestDashboardRepository;
        $this->patientRepository = $patientRepository;
    }

    public function getUpcomingReauthorizationRequests(GetUpcomingReauthorizationRequestsRequest $request): JsonResponse
    {
        return response()->json($this->reauthorizationRequestDashboardRepository->getUpcomingReauthorizationRequests($request->validated()));
    }

    public function getSubmittedReauthorizationRequestForms(GetSubmittedReauthorizationRequestFormsRequest $request): JsonResponse
    {
        return response()->json($this->reauthorizationRequestDashboardRepository->getSubmittedReauthorizationRequestForms($request->validated()));
    }

    public function createReauthorizationRequestForm(CreateRequthorizationRequestFormRequest $request): JsonResponse
    {
        return response()->json($this->reauthorizationRequestDashboardRepository->createReauthorizationRequestFormWithoutDocument($request->input('patient_id')));
    }

    public function getStages(): JsonResponse
    {
        return response()->json($this->reauthorizationRequestDashboardRepository->getStages());
    }

    public function changeStage(ChangeStageRequest $request, SubmittedReauthorizationRequestForm $form): JsonResponse
    {
        return response()->json($this->reauthorizationRequestDashboardRepository->changeStage($form, $request->validated()));
    }

    public function getExpirationsList(): JsonResponse
    {
        return response()->json(PatientInsurancePlan::getExpirationsList());
    }

    public function createLog(CreateLogRequest $request, SubmittedReauthorizationRequestForm $form): JsonResponse
    {
        return response()->json($this->reauthorizationRequestDashboardRepository->createLog($form, $request->validated()));
    }

    public function saveFutureInsuranceReauthorizationData(SaveFutureInsuranceReauthorizationDataRequest $request, SubmittedReauthorizationRequestForm $form): JsonResponse
    {
        return response()->json($this->reauthorizationRequestDashboardRepository->saveFutureInsuranceReauthorizationData($form, $request->validated()));
    }

    public function updateAuthNumber(UpdateAuthNumberRequest $request): JsonResponse
    {
        return response()->json($this->patientRepository->updateAuthNumber($request));
    }
}

<?php

namespace App\Http\Controllers\Api\NewPatientsCrm;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientInquiry\Api\ChangeStageRequest;
use App\Http\Requests\PatientInquiry\Api\CreateRequest;
use App\Http\Requests\PatientInquiry\Api\UpdateRequest;
use App\Http\Requests\PatientInquiry\Api\CreateInitialSurveyCommentRequest;
use App\Http\Requests\PatientInquiry\Api\CreateOnboardingCompleteCommentRequest;
use App\Http\Requests\PatientInquiry\Api\CreateSecondSurveyCommentRequest;
use App\Http\Requests\PatientInquiry\Api\GetArchiveInquiriesRequest;
use App\Http\Requests\PatientInquiry\Api\GetInquiriesByStageRequest;
use App\Models\Patient\Inquiry\PatientInquiry;
use App\Repositories\NewPatientsCRM\PatientInquiry\PatientInquiryRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientInquiryController extends Controller
{
    protected $patientInquiryRepository;

    public function __construct(PatientInquiryRepositoryInterface $patientInquiryRepository)
    {
        $this->patientInquiryRepository = $patientInquiryRepository;
    }

    public function getInquiriesByStage(GetInquiriesByStageRequest $request): JsonResponse
    {
        return response()->json($this->patientInquiryRepository->getInquiries($request->validated()));
    }

    public function getArchiveInquiries(GetArchiveInquiriesRequest $request): JsonResponse
    {
        return response()->json($this->patientInquiryRepository->getInquiries($request->validated(), true));
    }

    public function create(CreateRequest $request): JsonResponse
    {
        return response()->json($this->patientInquiryRepository->create($request->validated()));
    }

    public function update(UpdateRequest $request): JsonResponse
    {
        return response()->json($this->patientInquiryRepository->update($request->validated()));
    }

    public function createPatientFromPatientLead(PatientInquiry $inquiry): JsonResponse
    {
        return response()->json($this->patientInquiryRepository->createPatientFromPatientLead($inquiry));
    }

    public function changeStage(PatientInquiry $inquiry, ChangeStageRequest $request): JsonResponse
    {
        return response()->json($this->patientInquiryRepository->changeStage($inquiry, $request->validated()));
    }

    public function close(PatientInquiry $inquiry): JsonResponse
    {
        return response()->json($this->patientInquiryRepository->close($inquiry));
    }

    public function archive(PatientInquiry $inquiry, Request $request): JsonResponse
    {
        $this->patientInquiryRepository->archive($inquiry, $request->all());

        return response()->json($inquiry);
    }

    public function getComments(PatientInquiry $inquiry): JsonResponse
    {
        return response()->json($this->patientInquiryRepository->getComments($inquiry));
    }

    public function createComment(PatientInquiry $inquiry, Request $request): JsonResponse
    {
        return response()->json($this->patientInquiryRepository->createComment($inquiry, $request->all()));
    }

    public function createInitialSurveyComment(CreateInitialSurveyCommentRequest $request, PatientInquiry $inquiry): JsonResponse
    {
        return response()->json($this->patientInquiryRepository->createInitialSurveyComment($inquiry, $request->validated()));
    }

    public function createSecondSurveyComment(CreateSecondSurveyCommentRequest $request, PatientInquiry $inquiry): JsonResponse
    {
        return response()->json($this->patientInquiryRepository->createSecondSurveyComment($inquiry, $request->validated()));
    }

    public function createOnboardingCompleteComment(CreateOnboardingCompleteCommentRequest $request, PatientInquiry $inquiry): JsonResponse
    {
        return response()->json($this->patientInquiryRepository->createOnboardingCompleteComment($inquiry, $request->validated()));
    }

    public function getCompletedInitialAppointment(PatientInquiry $inquiry): JsonResponse
    {
        return response()->json($this->patientInquiryRepository->getCompletedInitialAppointment($inquiry));
    }

    public function getCompletedAppointments(PatientInquiry $inquiry): JsonResponse
    {
        return response()->json($inquiry->getCompletedAppointments());
    }
}
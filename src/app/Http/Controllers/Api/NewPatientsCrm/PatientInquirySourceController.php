<?php

namespace App\Http\Controllers\Api\NewPatientsCrm;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSourceRequest;
use App\Repositories\NewPatientsCRM\PatientInquirySource\PatientInquirySourceRepositoryInterface;
use Illuminate\Http\JsonResponse;

class PatientInquirySourceController extends Controller
{
    protected $patientInquirySourceRepository;

    public function __construct(PatientInquirySourceRepositoryInterface $patientInquirySourceRepository)
    {
        $this->patientInquirySourceRepository = $patientInquirySourceRepository;
    }

    public function index(): JsonResponse
    {
        return response()->json($this->patientInquirySourceRepository->getAll());
    }

    public function createSource(CreateSourceRequest $request): JsonResponse
    {
        $source = $this->patientInquirySourceRepository->createSource($request->validated());

        return response()->json($source);
    }
}
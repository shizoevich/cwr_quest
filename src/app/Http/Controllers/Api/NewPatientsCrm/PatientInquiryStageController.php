<?php

namespace App\Http\Controllers\Api\NewPatientsCrm;

use App\Http\Controllers\Controller;
use App\Repositories\NewPatientsCRM\PatientInquiryStage\PatientInquiryStageRepositoryInterface;
use Illuminate\Http\JsonResponse;

class PatientInquiryStageController extends Controller
{
    protected $patientInquiryStageRepository;

    public function __construct(PatientInquiryStageRepositoryInterface $patientInquiryStageRepository)
    {
        $this->patientInquiryStageRepository = $patientInquiryStageRepository;
    }

    public function index(): JsonResponse
    {
        return response()->json($this->patientInquiryStageRepository->getAll());
    }
}
<?php

namespace App\Http\Controllers\Api\NewPatientsCrm;

use App\Http\Controllers\Controller;
use App\Models\Patient\Lead\PatientLeadComment;
use App\Repositories\NewPatientsCRM\PatientLeadComment\PatientLeadCommentRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PatientLeadCommentController extends Controller
{
    protected $patientInquiryCommentRepository;

    public function __construct(PatientLeadCommentRepositoryInterface $patientInquiryCommentRepository)
    {
        $this->patientInquiryCommentRepository = $patientInquiryCommentRepository;
    }

    public function update(PatientLeadComment $comment, Request $request): JsonResponse
    {
        return response()->json($this->patientInquiryCommentRepository->update($comment, $request->all()));
    }

    public function destroy(PatientLeadComment $comment): JsonResponse
    {
        $this->patientInquiryCommentRepository->delete($comment);

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
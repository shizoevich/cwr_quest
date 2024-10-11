<?php

namespace App\Http\Controllers\Api\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientTransfer\Index as IndexRequest;
use App\Http\Requests\PatientTransfer\TransferPatient as TransferPatientRequest;
use App\Repositories\PatientTransfer\PatientTransferRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PatientTransferController extends Controller
{
    protected $patientTransferRepository;

    public function __construct(PatientTransferRepositoryInterface $patientTransferRepository)
    {
        $this->patientTransferRepository = $patientTransferRepository;
    }

    public function index(IndexRequest $request): JsonResponse
    {
        return response()->json($this->patientTransferRepository->getActiveList($request->validated()), Response::HTTP_OK);
    }

    public function transferPatient(TransferPatientRequest $request): JsonResponse
    {
        return response()->json($this->patientTransferRepository->transferPatient($request->validated()), Response::HTTP_CREATED);
    }
}

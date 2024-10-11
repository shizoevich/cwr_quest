<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\PatientInsuranceProcedure\Index as IndexRequest;
use App\Repositories\PatientInsurancesProcedure\PatientInsuranceProcedureRepositoryInterface;
use App\Http\Controllers\Controller;

class PatientInsuranceProcedureController extends Controller
{
    /**
     * @var PatientInsuranceProcedureRepositoryInterface
     */
    protected $insuranceProcedureRepository;

    /**
     * PatientInsuranceProcedureController constructor.
     * @param PatientInsuranceProcedureRepositoryInterface $insuranceProcedureRepository
     */
    public function __construct(PatientInsuranceProcedureRepositoryInterface $insuranceProcedureRepository)
    {
        $this->insuranceProcedureRepository = $insuranceProcedureRepository;
    }

    /**
     * @param IndexRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(IndexRequest $request)
    {
        return response()->json([
            'insurance_procedures' => $this->insuranceProcedureRepository->all(),
        ]);
    }
}

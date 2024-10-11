<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\PatientInsurance\Index as IndexRequest;
use App\Repositories\PatientInsurance\PatientInsuranceRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PatientInsuranceController extends Controller
{
    /**
     * @var PatientInsuranceRepositoryInterface
     */
    protected $patientInsuranceRepository;

    /**
     * PatientInsuranceController constructor.
     * @param PatientInsuranceRepositoryInterface $patientInsuranceRepository
     */
    public function __construct(PatientInsuranceRepositoryInterface $patientInsuranceRepository)
    {
        $this->patientInsuranceRepository = $patientInsuranceRepository;
    }

    /**
     * @param IndexRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(IndexRequest $request)
    {
        return response()->json($this->patientInsuranceRepository->all($request->limit, $request->search_query));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInsuranceList()
    {
        return response()->json($this->patientInsuranceRepository->getInsuranceList());
    }
}

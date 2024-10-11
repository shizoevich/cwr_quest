<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Patient;
use App\Repositories\Patient\PatientRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CheckPatientLateCancellationPaymentController extends Controller
{
    /**
     * @var PatientRepositoryInterface
     */
    protected $patientRepository;

    /**
     * PatientController constructor.
     * @param PatientRepositoryInterface $patientRepository
     */
    public function __construct(PatientRepositoryInterface $patientRepository)
    {
        $this->patientRepository = $patientRepository;
    }

    /**
     * @param IndexRequest $request
     * @return JsonResponse
     */
    public function index(Patient $patient): JsonResponse
    {
        return response()->json($patient->canChargeLateCancellationFee());
    }
}

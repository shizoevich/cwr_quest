<?php

namespace App\Http\Controllers\Api\Patient;

use App\Http\Requests\Patient\Api\Show as ShowRequest;
use App\Enums\State;
use App\Http\Requests\Patient\Api\Index as IndexRequest;
use App\Http\Requests\Patient\Api\Store as StoreRequest;
use App\Http\Requests\Patient\Api\UpdateRequest;
use App\Http\Requests\Patient\Api\UpdateSecondaryEmail as UpdateSecondaryEmailRequest;
use App\Http\Requests\Patient\Api\UpdatePatientVisitFrequency as UpdatePatientVisitFrequencyRequest;
use App\Http\Requests\Patient\Api\UpdateAttachedProviders as UpdateAttachedProvidersRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\Api\StorePatientAlertRequest;
use App\Patient;
use App\Repositories\Fax\FaxRepositoryInterface;
use App\Repositories\Patient\PatientRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class PatientController extends Controller
{
    /**
     * @var PatientRepositoryInterface
     */
    protected $patientRepository;
    protected $faxRepository;

    /**
     * PatientController constructor.
     * @param PatientRepositoryInterface $patientRepository
     */
    public function __construct(PatientRepositoryInterface $patientRepository, FaxRepositoryInterface $faxRepository) 
    {
        $this->patientRepository = $patientRepository;
        $this->faxRepository = $faxRepository;
    }

    /**
     * @param IndexRequest $request
     * @return JsonResponse
     */
    public function index(IndexRequest $request): JsonResponse
    {
        return response()->json([
            'patients' => $this->patientRepository->all((int)$request->limit, $request->search_query, Auth::user())
        ]);
    }

    /**
     * @param ShowRequest $request
     * @param Patient $patient
     * @return JsonResponse
     */
    public function show(ShowRequest $request, Patient $patient): JsonResponse
    {
        return response()->json([
            'patient' => $this->patientRepository->show($patient)
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function states(): JsonResponse
    {
        return response()->json([
            'states' => State::$list,
        ]);
    }

    /**
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        return response()->json([
            'patient' => $this->patientRepository->create($request->all())
        ]);
    }


    /**
     * @param UpdateRequest $request
     * @param Patient $patient
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, Patient $patient): JsonResponse
    {
        return response()->json([
            'patient' => $this->patientRepository->update($request->all(), $patient)
        ]);
    }
    
    /**
     * @param UpdateAttachedProvidersRequest $request
     * @param Patient                        $patient
     *
     * @return JsonResponse
     */
    public function updateAttachedProviders(UpdateAttachedProvidersRequest $request, Patient $patient): JsonResponse
    {
        return response()->json([
            'patient' => $this->patientRepository->updateAttachedProviders($request->all(), $patient)
        ]);
    }

    /**
     * @param UpdateRequest $request
     * @return JsonResponse
     */
    public function updatePatientLanguagePrefer(UpdateRequest $request): JsonResponse
    {
        $data = $this->patientRepository->updatePatientLanguagePrefer($request);

        return new JsonResponse(['message' => "Change Language", 'status' => Response::HTTP_OK, 'data' => $data]);
    }

    /**
     * @param UpdateRequest $request
     * @return JsonResponse
     */
    public function addSecondaryEmail(UpdateSecondaryEmailRequest $request): JsonResponse
    {
        $data = $this->patientRepository->addPatientSecondaryEmail($request);

        return new JsonResponse([
            'message' => "Add Patient Secondary Email",
            'status' => Response::HTTP_OK,
            'data' => $data
        ]);
    }

    public function updatePatientVisitFrequency(UpdatePatientVisitFrequencyRequest $request): JsonResponse
    {
        $this->patientRepository->updatePatientVisitFrequency($request);

        return new JsonResponse([
            'message' => "Update Patient Visit Frequency",
            'status' => Response::HTTP_OK
        ]);
    }

    public function addPatientAlert(StorePatientAlertRequest $request): JsonResponse
    {
        return response()->json(
            $this->patientRepository->addPatientAlertData($request->validated())
        );
    }

    public function getFaxes(Patient $patient): JsonResponse
    {
        return response()->json($this->faxRepository->getFaxesForEntity($patient));
    }

    public function checkIsSynchronized(Patient $patient): JsonResponse
    {
        return response()->json($this->patientRepository->checkIsSynchronized($patient));
    }
}

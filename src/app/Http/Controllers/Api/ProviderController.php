<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\GetPatients;
use App\Http\Requests\Provider\Index;
use App\Http\Requests\Provider\Update;
use App\Provider;
use App\Repositories\Provider\ProviderRepositoryInterface;
use App\TherapistSurvey;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProviderController extends Controller
{
    /**
     * @var ProviderRepositoryInterface
     */
    protected $providerRepository;

    /**
     * ProviderController constructor.
     * @param ProviderRepositoryInterface $providerRepository
     */
    public function __construct(ProviderRepositoryInterface $providerRepository)
    {
        $this->providerRepository = $providerRepository;
    }

    /**
     * @param Update $request
     * @param Provider $provider
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Update $request, Provider $provider)
    {
        $allowUpdate = [
            'tridiuum_sync_availability',
            'tridiuum_sync_appointments',
        ];
        $data = array_only($request->all(), $allowUpdate);

        if (!empty($data)) {
            $provider->update($data);
        }

        return response()->json(null, 204);
    }

    /**
     * @param Index $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Index $request)
    {
        return response()->json([
            'providers' => $this->providerRepository->all(intval($request->limit), $request->search_query, ['diagnoses' => $request->input('diagnoses') ?? []])
        ]);
    }

    public function isTherapistCustomTimesheet(){
        return response()->json( $this->providerRepository->getTherapistCustomTimesheetOption()); 
    }

    public function getPatients(GetPatients $request, Provider $provider): JsonResponse
    {
        return response()->json([
            'data' => $this->providerRepository->getPatientsByProvider($provider, $request->validated()),
        ]);
    }
}

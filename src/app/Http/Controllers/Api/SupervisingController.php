<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supervising\StoreIsSupervisorRequest;
use App\Http\Requests\Supervising\AttachSupervisorRequest;
use App\Http\Requests\Supervising\GetSuperviseesRequest;
use App\Provider;
use App\Repositories\Provider\SupervisorRepositoryInterface;

class SupervisingController extends Controller
{
    protected $supervisorRepository;

    public function __construct(SupervisorRepositoryInterface $supervisorRepository)
    {
        $this->supervisorRepository = $supervisorRepository;
    }

    public function getSupervisors()
    {
        return response()->json($this->supervisorRepository->getSupervisors());
    }

    public function getSupervisees(GetSuperviseesRequest $request)
    {
        $user = \Auth::user();

        if ($user->isAdmin()) {
            if (empty($request->input('supervisor_id'))) {
                return response();
            }
            $supervisorId = $request->input('supervisor_id');
        } else {
            $supervisorId = $user->provider_id;
        }

        return response()->json($this->supervisorRepository->getSupervisees($supervisorId));
    }

    public function storeIsSupervisor(StoreIsSupervisorRequest $request, Provider $provider)
    {
        $this->supervisorRepository->storeIsSupervisor($request->input('is_supervisor'), $provider);

        return response([
            'success' => true,
        ], 200);
    }

    public function attachSupervisor(AttachSupervisorRequest $request, Provider $provider)
    {
        $supervisorId = $request->input('supervisor_id');
        if ($supervisorId && !$this->supervisorRepository->checkSuperviseeLimit($supervisorId)) {
            return response([
                'success' => false,
                'message' => 'Cannot assign supervisor, because he/she already has 6 supervisee.',
            ], 400);
        };

        $this->supervisorRepository->attachSupervisor($request->validated(), $provider);

        return response([
            'success' => true,
        ], 200);
    }
}

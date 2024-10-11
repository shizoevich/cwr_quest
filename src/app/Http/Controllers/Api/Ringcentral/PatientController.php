<?php

namespace App\Http\Controllers\Api\Ringcentral;

use App\Http\Controllers\Controller;
use App\Repositories\Ringcentral\PatientRingcentralRepositoryInterface;
use App\Http\Requests\Fax\PatientAttachRequest;
use App\Http\Requests\Fax\PatientDetachRequest;
use App\Http\Requests\Fax\PatientSearchRequest;

class PatientController extends Controller
{
    private $patientRepository;

    public function __construct(PatientRingcentralRepositoryInterface $patientRepository)
    {
        $this->patientRepository = $patientRepository;
    }

    public function index(PatientSearchRequest $request)
    {
        return $this->patientRepository->getPatients($request);
    }

    public function attach(PatientAttachRequest $request)
    {
        return $this->patientRepository->attachPatient($request);
    }

    public function dettach(PatientDetachRequest $request)
    {
        return $this->patientRepository->dettachPatient($request);
    }
}

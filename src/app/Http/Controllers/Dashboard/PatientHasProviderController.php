<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctors\AddPatientProviderRelationshipRequest;
use App\Http\Requests\Doctors\DeletePatientProviderRelationshipRequest;
use App\Repositories\PatientHasProvider\PatientHasProviderRepositoryInterface;

class PatientHasProviderController extends Controller
{
    protected $patientHasProviderRepository;

    public function __construct(PatientHasProviderRepositoryInterface $patientHasProviderRepository)
    {
        $this->patientHasProviderRepository = $patientHasProviderRepository;
    }

    public function deletePatientProviderRelationship(DeletePatientProviderRelationshipRequest $request) {
        $status = $this->patientHasProviderRepository->deletePatientProviderRelationship($request->validated());

        return response(['status' => $status]);
    }

    public function addPatientProviderRelationship(AddPatientProviderRelationshipRequest $request) {
        $model = $this->patientHasProviderRepository->addPatientProviderRelationship($request->validated());

        return response(['model' => $model]);
    }
}

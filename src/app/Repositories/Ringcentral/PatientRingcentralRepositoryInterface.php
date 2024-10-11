<?php

namespace App\Repositories\Ringcentral;

use App\Http\Requests\Fax\PatientAttachRequest;
use App\Http\Requests\Fax\PatientDetachRequest;
use App\Http\Requests\Fax\PatientSearchRequest;

interface PatientRingcentralRepositoryInterface
{
    public function getPatients(PatientSearchRequest $request);

    public function attachPatient(PatientAttachRequest $request);

    public function dettachPatient(PatientDetachRequest $request);
}
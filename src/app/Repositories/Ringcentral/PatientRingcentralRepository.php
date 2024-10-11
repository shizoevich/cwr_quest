<?php

namespace App\Repositories\Ringcentral;

use App\Models\FaxModel\Fax;
use App\Patient;
use App\Http\Requests\Fax\PatientAttachRequest;
use App\Http\Requests\Fax\PatientDetachRequest;
use App\Http\Requests\Fax\PatientSearchRequest;
use App\Models\Patient\Lead\PatientLead;
use App\PatientDocument;
use App\PatientStatus;
use App\Repositories\Fax\FaxRepositoryInterface;
use App\Traits\ReplaceSpecialSymbolsTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

class PatientRingcentralRepository implements PatientRingcentralRepositoryInterface
{
    use ReplaceSpecialSymbolsTrait;

    protected $faxRepository;

    public function __construct(FaxRepositoryInterface $faxRepository)
    {
        $this->faxRepository = $faxRepository;
    }

    public function getPatients(PatientSearchRequest $request): JsonResponse
    {
        $searchPatientByName = $request->input('search');

        $patientQuery = Patient::query()->select('id', 'first_name', 'last_name')->selectRaw("'Patient' as model")->limit(500);
        $patientLeadQuery = PatientLead::query()->select('id', 'first_name', 'last_name')->selectRaw("'PatientLead' as model");

        //search request
        if ($searchPatientByName && trim($searchPatientByName) !== '') {
            $searchPatientByName = $this->replaceSpecialCharacters($searchPatientByName);
            $patientQuery->search($searchPatientByName);
            $patientLeadQuery->search($searchPatientByName);
        }

        $patients = $patientQuery->get();
        $patientsLeads = $patientLeadQuery->get();

        $combinedResults = $patients->merge($patientsLeads)->sortBy('full_name')->toArray();

        $data = array_slice($combinedResults, 0, 15);
        $patientData = [];

        foreach ($data as $patient) {
            $patientData[] = [
                'id' => $patient['id'],
                'patient' => $patient['first_name'] . " " . $patient['last_name'],
                'patient_status' =>  $patient['model'] === 'Patient' ? PatientStatus::where('id',  Patient::whereId($patient['id'])->first()->status_id)->first()->status : '',
            ];
        }

        if (count($patientData) > 0) {
            $results = new LengthAwarePaginator(
                $patientData,
                count($combinedResults),
                15,
                1
            );
            return new JsonResponse($results);
        }

        return new JsonResponse(
            [
                'message' => "data api not found",
                'status' => Response::HTTP_NOT_FOUND,
            ],
            404
        );
    }

    public function attachPatient(PatientAttachRequest $request)
    {
        $patient = Patient::find($request->input('patient_id'));
        $fax = Fax::find($request->input('fax_id'));
        $faxDocument = PatientDocument::withoutAdminScope()->where('aws_document_name', $fax->file_name)->first();

        $data = [
            'comment' => $request->input('comment'),
            'status' => $request->input('status'),
            'fax_name' => $faxDocument->original_document_name,
            'only_for_admin' => $faxDocument->only_for_admin,
        ];

        return $this->faxRepository->attachFax($data, $patient, $fax);
    }

    public function dettachPatient(PatientDetachRequest $request)
    {
        $fax = Fax::find($request->input('fax_id'));

        return $this->faxRepository->detachFax($fax->patient, $fax);
    }
}

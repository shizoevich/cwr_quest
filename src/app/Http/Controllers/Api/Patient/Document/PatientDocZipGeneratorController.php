<?php

namespace App\Http\Controllers\Api\Patient\Document;

use App\Http\Controllers\Controller;
use App\Http\Requests\ZipArchive\PatientDocZipGeneratorRequest;
use App\Patient; 
use App\Repositories\Patient\PatientDocZipGenerate\PatientDocZipGenerateRepositoryInterface;

class PatientDocZipGeneratorController extends Controller
{
    protected $zipRepository;

    public function __construct(PatientDocZipGenerateRepositoryInterface $zipRepository)
    {
        $this->zipRepository = $zipRepository;
    }

    public function generatePatientDocZip(PatientDocZipGeneratorRequest $request, Patient $patient)
    {
        try {
            return $this->zipRepository->createPatientDocZip($request->validated(), $patient);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function downloadPatientDocZip(Patient $patient, string $fileName)
    {
        try {
            return $this->zipRepository->getPatientDocZip($patient, $fileName);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

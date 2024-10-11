<?php

namespace App\Http\Controllers;

use App\Components\PatientForm\ConfidentialInformation\ConfidentialInformationForm as ConfidentialDocumentMaker;
use App\Patient;
use App\PatientDocumentType;
use Illuminate\Http\Request;

class PatientFormSecondController
{
    
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Mpdf\MpdfException
     */
    public function saveForm(Request $request)
    {
        $patient = Patient::findOrFail($request->input('patient_id'));
        
        $newFileName = md5(uniqid(time())) . '.pdf';
        $dtID = PatientDocumentType::getAuthToReleaseId();
        $documentMaker = new ConfidentialDocumentMaker($request->all());
        $documentMaker->fillDocument($newFileName);
        $patient->documents()->create([
            'original_document_name' => $request->name . " - Authorization to Release Confidential Information.pdf",
            'aws_document_name' => $newFileName,
            'document_type_id' => $dtID,
        ]);
        
        
        return response()->json([
            'success' => true
        ], 201);
    }
    
}

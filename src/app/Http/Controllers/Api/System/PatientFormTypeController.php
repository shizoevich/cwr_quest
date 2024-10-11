<?php


namespace App\Http\Controllers\Api\System;


use App\Http\Controllers\Controller;
use App\Models\Patient\DocumentRequest\PatientFormType;
use App\Patient;

class PatientFormTypeController extends Controller
{
    public function index()
    {
        return PatientFormType::orderBy('order')->get()->values();
    }
    
    public function indexByPatient(Patient $patient)
    {
        return PatientFormType::orderBy('order')
            ->get()
            ->transform(function(PatientFormType $formType) use ($patient) {
                $formType->has_filled_document = $formType->hasFilledDocument($patient);
                if($formType->name === 'supporting_documents') {
                    $formType->is_required = $formType->is_required && (!$formType->has_filled_document['has_insurance'] || !$formType->has_filled_document['has_driver_license']);
                } else {
                    $formType->is_required = $formType->is_required && !$formType->has_filled_document;
                }
                
                return $formType;
            });
    }
}
<?php

namespace App\Http\Controllers\Api\NewPatientsCrm;

use App\Http\Controllers\Controller;
use App\Models\Patient\Inquiry\PatientInquiryRegistrationMethod;
use Illuminate\Http\JsonResponse;

class PatientInquiryRegistrationMethodController extends Controller
{

    public function index(): JsonResponse
    {
        $methods = PatientInquiryRegistrationMethod::all();
        return response()->json($methods);
    }
}
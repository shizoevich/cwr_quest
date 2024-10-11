<?php

namespace App\Http\Controllers\Api\NewPatientsCrm;

use App\Http\Controllers\Controller;
use App\Models\Patient\Inquiry\PatientInquiryChannel;
use Illuminate\Http\JsonResponse;

class PatientInquiryChannelController extends Controller
{

    public function index(): JsonResponse
    {
        $channels = PatientInquiryChannel::all();
        return response()->json($channels);
    }
}
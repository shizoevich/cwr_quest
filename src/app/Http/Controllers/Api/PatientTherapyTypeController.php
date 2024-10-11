<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PatientTherapyType;

class PatientTherapyTypeController extends Controller
{
    public function index()
    {
        return response()->json(['therapy_types' => PatientTherapyType::all()]);
    }
}

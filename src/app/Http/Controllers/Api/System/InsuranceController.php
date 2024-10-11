<?php

namespace App\Http\Controllers\Api\System;

use App\Http\Controllers\Controller;
use App\PatientInsurance;

class InsuranceController extends Controller
{
    public function index()
    {
        $insurances = PatientInsurance::query()->with('plans', 'plans.childPlans')->orderBy('insurance')->get();
        
        return response()->json([
            'insurances' => $insurances
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api\Availability;

use App\AvailabilitySubtype;
use App\AvailabilityType;
use App\Http\Controllers\Controller;

class AvailabilityTypeController extends Controller
{
    public function index()
    {
        $types = AvailabilityType::all();

        return response()->json($types);
    }

    public function showSubtypes()
    {
        $types = AvailabilitySubtype::orderBy('index', 'asc')->get();

        return response()->json($types);
    }
}

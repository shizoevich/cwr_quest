<?php

namespace App\Http\Controllers\Api\OfficeAllyTransaction;

use App\Http\Controllers\Controller;
use App\Models\Officeally\OfficeallyTransactionPurpose;
use Illuminate\Http\JsonResponse;

class OfficeAllyTransactionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(OfficeallyTransactionPurpose::all());
    }
}

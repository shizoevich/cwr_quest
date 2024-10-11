<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientTransactions\StoreTransactionAdjustment;
use App\Models\Patient\PatientTransactionAdjustment;
use App\Jobs\Patients\CalculatePatientBalance;

class PatientTransactionAdjustmentController extends Controller
{

    /**
     * @param StoreTransactionAdjustment $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreTransactionAdjustment $request) {
        PatientTransactionAdjustment::addAdjustment($request->patient_id, $request->amount, $request->comment, auth()->id());
        \Bus::dispatchNow(new CalculatePatientBalance([$request->patient_id]));

        return response([], 201);
    }

}

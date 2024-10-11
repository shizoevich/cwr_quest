<?php


namespace App\Http\Controllers\Api\PatientForm;


use App\Http\Controllers\Controller;
use App\Patient;
use Carbon\Carbon;

class PatientController extends Controller
{
    /**
     * @param Patient $encryptPatient
     * @return Patient|array
     */
    public function show(Patient $encryptPatient)
    {
        $encryptPatient->load([
            'squareAccount',
            'squareAccount.cards',
            'informationForm'
        ]);
        $encryptPatient = $encryptPatient->toArray();
        $encryptPatient['date_of_birth'] = Carbon::parse($encryptPatient['date_of_birth'])->format('m/d/Y');
        $encryptPatient['id'] = encrypt($encryptPatient['id']);

        return $encryptPatient;
    }
}
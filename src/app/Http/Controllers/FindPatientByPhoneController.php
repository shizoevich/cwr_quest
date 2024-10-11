<?php

namespace App\Http\Controllers;

use App\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class FindPatientByPhoneController extends Controller
{
    public function findPatientByPhone(Request $request)
    {
        $phoneNumber = $request->query('number');
        if (!$phoneNumber) {
            return Redirect::to('/chart?error-phone=' . $phoneNumber);
        }

        $user = Auth::user();
        $phone = sanitize_phone($phoneNumber);
        $patientsQuery = Patient::query();
        
        if (!$user->isAdmin()) {
            $patientsQuery = $patientsQuery->where('provider_id', $user->provider_id);
        }

        $patient = $patientsQuery->where(function ($query) use ($phone) {
            $query->where('home_phone', 'like', "%$phone%")
                ->orWhere('cell_phone', 'like', "%$phone%")
                ->orWhere('work_phone', 'like', "%$phone%");
        })->first();

        if (isset($patient)) {
            return Redirect::to('/chart/' . $patient->id);
        }

        return Redirect::to('/chart?error-phone=' . $phoneNumber);
    }
}

<?php

namespace App\Http\Controllers\Api\Availability\Admin;

use App\Availability;
use App\Http\Requests\Availability\Admin\Show;
use App\Jobs\Availability\GetDoctorsAvailability;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\PatientInsurance;
use App\User;

class AvailabilityController extends Controller
{
    /**
     * Copied from another place
     *
     * @param Show $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Show $request)
    {
        $startDate = Carbon::parse($request->get('start'));
        $endDate = Carbon::parse($request->get('end'));

        $data =  \Bus::dispatchNow(new GetDoctorsAvailability($startDate, $endDate, $request->all(), true));

        return response()->json($data);
    }

    public function getProviderInsurances(Request $request)
    {
        $insurances = User::query()
            ->select('patient_insurances.*')
            ->join('therapist_survey', 'therapist_survey.user_id', 'users.id')
            ->join('therapist_has_insurances', 'therapist_has_insurances.therapist_id', 'therapist_survey.id')
            ->join('patient_insurances', 'patient_insurances.id', 'therapist_has_insurances.insurance_id')
            ->where('users.provider_id', $request->providerId)
            ->get();

        return response()->json($insurances);
    }

    public function getInsuranceProviders(Request $request)
    {
        $providers = PatientInsurance::query()
            ->select('providers.*')
            ->join('therapist_has_insurances', 'therapist_has_insurances.insurance_id', 'patient_insurances.id')
            ->join('therapist_survey', 'therapist_survey.id', 'therapist_has_insurances.therapist_id')
            ->join('users', 'users.id', 'therapist_survey.user_id')
            ->join('providers', 'providers.id', 'users.provider_id')
            ->where('patient_insurances.id', $request->insuranceId)
            ->get();

        return response()->json($providers);
    }
}

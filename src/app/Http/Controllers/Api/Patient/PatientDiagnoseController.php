<?php


namespace App\Http\Controllers\Api\Patient;


use App\Events\NeedsWriteSystemComment;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\Diagnose\Update;
use App\Jobs\Patients\UpdateDiagnoses;
use App\Models\Diagnose;
use App\Option;
use App\Patient;

class PatientDiagnoseController extends Controller
{
    /**
     * @param Update  $request
     * @param Patient $patient
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Update $request, Patient $patient)
    {
        \Bus::dispatchNow(new UpdateDiagnoses($patient, $request->input('diagnoses'), false));
        $stats = $patient->diagnoses()->sync($request->input('diagnoses'));
        if(count($stats['attached']) > 0 || count($stats['detached']) > 0) {
            $userName = auth()->user()->provider ? auth()->user()->provider->provider_name : auth()->user()->meta->firstname . ' ' . auth()->user()->meta->lastname;
            event(new NeedsWriteSystemComment($patient->id, trans('comments.diagnose_changed_by_provider', ['provider_name' => $userName])));
        }
        
        
        return response()->json([
            'patient' => $patient,
        ]);
    }
}
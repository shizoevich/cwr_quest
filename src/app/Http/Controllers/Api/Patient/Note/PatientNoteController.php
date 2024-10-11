<?php


namespace App\Http\Controllers\Api\Patient\Note;


use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\Note\GetDraftNotes;
use App\Patient;
use App\PatientNote;
use Illuminate\Http\JsonResponse;

class PatientNoteController extends Controller
{
    /**
     * @param GetDraftNotes $request
     * @param Patient $patient
     * @return JsonResponse
     */
    public function getDraft(GetDraftNotes $request, Patient $patient)
    {
        $notes = $patient->patientNotes()
            ->select([
                'id',
                'date_of_service',
                'created_at'
            ])
            ->onlyNotFinalized()
            ->where('provider_id', auth()->user()->provider_id)
            ->get();
        
        return response()->json($notes);
    }
}
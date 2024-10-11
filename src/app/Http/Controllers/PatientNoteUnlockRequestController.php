<?php

namespace App\Http\Controllers;

use App\Http\Requests\Patient\Note\UnlockRequest\Accept as AcceptUnlockRequest;
use App\Http\Requests\Patient\Note\UnlockRequest\Cancel as CancelUnlockRequest;
use App\Http\Requests\Patient\Note\UnlockRequest\Decline as DeclineUnlockRequest;
use App\Http\Requests\Patient\Note\UnlockRequest\Send as SendUnlockRequest;
use App\Models\Patient\PatientNoteUnlockRequest;
use App\PatientNote;
use App\Repositories\Patient\PatientNoteUnlockRequestRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PatientNoteUnlockRequestController extends Controller
{
    protected $patientNoteUnlockRequestRepository;

    public function __construct(PatientNoteUnlockRequestRepositoryInterface $patientNoteUnlockRequestRepository)
    {
        $this->patientNoteUnlockRequestRepository = $patientNoteUnlockRequestRepository;
    }

    public function index(): View
    {
        return view('dashboard.pending-requests.patient-note-unlock-requests', [
            'newRequests' => $this->patientNoteUnlockRequestRepository->getNewRequests(),
            'checkedRequests' => $this->patientNoteUnlockRequestRepository->getCheckedRequests(),
        ]);
    }

    public function activeRequests(int $patientNoteId): JsonResponse
    {
        if (is_null(auth()->user()->provider_id)) {
            response()->json([], 204);
        }

        return response()->json($this->patientNoteUnlockRequestRepository->getActiveRequests($patientNoteId));
    }

    public function send(SendUnlockRequest $request): JsonResponse
    {
        $patientNote = PatientNote::find($request->patient_note_id);

        if ($patientNote->isEditable()) {
            return response()->json([
                'message' => 'Progress note is still editable.',
            ], 403 );
        }

        $existingRequestCount = PatientNoteUnlockRequest::query()
            ->where('patient_note_id', $patientNote->id)
            ->new()
            ->count();

        if ($existingRequestCount > 0) {
            return response()->json([
                'message' => 'Unlock request for this progress note already exists.',
            ], 403 );
        }

        $unlockRequest = $this->patientNoteUnlockRequestRepository->send($request->all(), $patientNote);

        return response()->json([
            'success' => !is_null($unlockRequest),
            'data' => $unlockRequest,
        ], 201);
    }

    public function accept(AcceptUnlockRequest $request)
    {
        $this->patientNoteUnlockRequestRepository->accept($request->all());

        $data = [
            'message' => 'Unlock Request has been Approved',
        ];

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        return redirect()->back()->with($data);
    }

    public function decline(DeclineUnlockRequest $request)
    {
        $this->patientNoteUnlockRequestRepository->decline($request->all());

        $data = [
            'message' => 'Unlock Request has been Declined',
        ];

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        return redirect()->back()->with($data);
    }

    public function cancel(CancelUnlockRequest $request): JsonResponse
    {
        $this->patientNoteUnlockRequestRepository->cancel($request->all());

        return response()->json([], 204);
    }
}
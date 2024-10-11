<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\AccessUtils;
use App\Models\Patient\PatientRemovalRequest;
use App\Repositories\Patient\PatientRemovalRequestRepositoryInterface;
use App\Http\Requests\PatientRemovalRequests\Send as SendRemovalRequest;
use App\Http\Requests\PatientRemovalRequests\Accept as AcceptRemovalRequest;
use App\Http\Requests\PatientRemovalRequests\Decline as DeclineRemovalRequest;
use App\Http\Requests\PatientRemovalRequests\Cancel as CancelRemovalRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PatientRemovalRequestController extends Controller
{
    use AccessUtils;

    protected $patientRemovalRequestRepository;

    public function __construct(PatientRemovalRequestRepositoryInterface $patientRemovalRequestRepository)
    {
        $this->patientRemovalRequestRepository = $patientRemovalRequestRepository;
    }

    public function index(): View
    {
        return view('dashboard.pending-requests.patient-removal-requests', [
            'newRequests' => $this->patientRemovalRequestRepository->getNewRequests(),
            'checkedRequests' => $this->patientRemovalRequestRepository->getCheckedRequests(),
        ]);
    }

    public function activeRequests(int $patientId): JsonResponse
    {
        if (is_null(auth()->user()->provider_id)) {
            response()->json([], 204);
        }

        return response()->json($this->patientRemovalRequestRepository->getActiveRequestsForPatient($patientId));
    }

    public function send(SendRemovalRequest $request): JsonResponse
    {
        if (!$this->isUserHasAccessRightsForPatient($request->patient_id)) {
            return response()->json([
                'message' => 'You cannot send remove request.',
            ], 403);
        }

        $existingRequestCount = PatientRemovalRequest::query()
            ->where('patient_id', $request->patient_id)
            ->where('provider_id', auth()->user()->provider_id)
            ->new()
            ->count();

        if ($existingRequestCount > 0) {
            return response()->json([
                'message' => 'Remove request for this patient already exists.',
            ], 403);
        }

        $removeRequest = $this->patientRemovalRequestRepository->send($request->all());

        return response()->json([
            'success' => !is_null($removeRequest),
            'data' => $removeRequest,
        ], 201);
    }

    /**
     * @param AcceptRemovalRequest $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function accept(AcceptRemovalRequest $request)
    {
        $this->patientRemovalRequestRepository->accept($request->all());

        $data = [
            'message' => 'Removal Request has been Approved',
        ];

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        return redirect()->back()->with($data);
    }

    public function decline(DeclineRemovalRequest $request): Response
    {
        $this->patientRemovalRequestRepository->decline($request->all());

        $data = [
            'message' => 'Removal Request has been Declined',
        ];

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        return redirect()->back()->with($data);
    }

    public function cancel(CancelRemovalRequest $request): Response
    {
        $this->patientRemovalRequestRepository->cancel($request->all());

        return response([], 204);
    }
}

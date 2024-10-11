<?php

namespace App\Http\Controllers\Api\Appointment;

use App\Appointment;
use App\Http\Requests\Appointments\Destroy as DestroyRequest;
use App\Http\Requests\Appointments\Show as ShowRequest;
use App\Http\Requests\Appointments\Index as IndexRequest;
use App\Http\Requests\Appointments\Store as StoreRequest;
use App\Http\Requests\Appointments\Update as UpdateRequest;
use App\Repositories\Appointment\Model\AppointmentRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class AppointmentController extends Controller
{
    /**
     * @var AppointmentRepositoryInterface
     */
    protected $appointmentRepository;

    /**
     * AppointmentController constructor.
     * @param AppointmentRepositoryInterface $appointmentRepository
     */
    public function __construct(AppointmentRepositoryInterface $appointmentRepository)
    {
        $this->appointmentRepository = $appointmentRepository;
    }

    /**
     * @param IndexRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(IndexRequest $request)
    {
        return response()->json([
            'appointments' => $this->appointmentRepository->all($request->all())
        ]);
    }
    
    /**
     * @param IndexRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function importantDirties(IndexRequest $request)
    {
        return response()->json([
            'dirties' => $this->appointmentRepository->importantDirties($request->input('ids') ?? [])
        ]);
    }

    /**
     * @param DestroyRequest $request
     * @param Appointment $appointment
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(DestroyRequest $request, Appointment $appointment   )
    {
        $this->appointmentRepository->delete($appointment);

        return response()->json(null, 204);
    }

    /**
     * @param ShowRequest $request
     * @param Appointment $appointment
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(ShowRequest $request, Appointment $appointment)
    {
        $appointment->append([
            'can_complete',
            'can_change_status',
            'can_reschedule',
            'can_cancel',
        ]);

        // @todo change logic when "upheal" integration will be finished
        $type = $request->query('type');
        $videoSession = $type === 'upheal' ? 'uphealMeet' : 'googleMeet';
        
        return response()->json([
            'appointment' => $appointment->load([
                $videoSession => function($query) {
                    $query->with('invitations')->orderBy('created_at', 'desc');
                },
                'patient',
                'officeRoom',
                'provider',
                'status'
            ])
        ]);
    }

    /**
     * @param StoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        return response()->json([
            'appointment' => $this->appointmentRepository->create($request->all())
        ], Response::HTTP_CREATED);
    }

    /**
     * @param UpdateRequest $request
     * @param Appointment $appointment
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, Appointment $appointment)
    {
        return response()->json([
            'appointment' => $this->appointmentRepository->update($request->validated(), $appointment)
        ]);
    }
    
    public function availableStatuses()
    {
        return response()->json([
            'statuses' => $this->appointmentRepository->getAvailableStatuses(),
        ]);
    }
}

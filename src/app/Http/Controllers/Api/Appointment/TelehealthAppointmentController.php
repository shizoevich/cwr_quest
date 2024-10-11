<?php

namespace App\Http\Controllers\Api\Appointment;

use App\Patient;
use App\Repositories\Appointment\Model\TelehealthAppointmentRepositoryInterface;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TelehealthAppointmentController extends Controller
{
    /**
     * @var TelehealthAppointmentRepositoryInterface
     */
    protected $appointmentRepository;

    /**
     * AppointmentController constructor.
     * @param TelehealthAppointmentRepositoryInterface $appointmentRepository
     */
    public function __construct(TelehealthAppointmentRepositoryInterface $appointmentRepository)
    {
        $this->appointmentRepository = $appointmentRepository;
    }
    
    /**
     * @param Request $request
     *
     * @param Patient $patient
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, Patient $patient)
    {
        $appointments = $this->appointmentRepository->upcomingByPatient($patient, auth()->user()->provider);
        
        return response()->json([
            'today' => $appointments->where('time', '>=', Carbon::today()->timestamp)->where('time', '<=', Carbon::today()->endOfDay()->timestamp)->values(),
            'upcoming' => $appointments->where('time', '>', Carbon::today()->endOfDay()->timestamp)->values(),
        ]);
    }
}

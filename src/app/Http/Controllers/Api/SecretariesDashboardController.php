<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\RestartTridiuumAppointmentsParserJob;
use App\Repositories\SecretariesDashboard\NewLostPatients\NewLostPatientsRepositoryInterface;
use App\Repositories\SecretariesDashboard\ImportantForToday\ImportantForTodayRepositoryInterface;
use App\Http\Requests\SecretariesDashboard\GetImportantForToday as GetImportantForTodayRequest;
use App\Option;
use App\KaiserAppointment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class SecretariesDashboardController extends Controller
{
    protected $importantForTodayRepository;

    protected $newLostPatientsRepository;

    public function __construct(
        ImportantForTodayRepositoryInterface $importantForTodayRepository,
        NewLostPatientsRepositoryInterface $newLostPatientsRepository
    )
    {
        $this->importantForTodayRepository = $importantForTodayRepository;
        $this->newLostPatientsRepository = $newLostPatientsRepository;
    }

    public function getImportantForToday(GetImportantForTodayRequest $request): JsonResponse
    {
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $withoutForms = $this->importantForTodayRepository->getAppointmentsWithoutForms($startDate, $endDate);
        $withRequiredEligibility = $this->importantForTodayRepository->getAppointmentsWithRequiredEligibility($startDate, $endDate);
        $withDeductible = $this->importantForTodayRepository->getAppointmentsWithDeductible($startDate, $endDate);
        $withNegativeBalance = $this->importantForTodayRepository->getAppointmentsWithNegativeBalance($startDate, $endDate);
        $patientLastAppointments = $this->importantForTodayRepository->getPatientLastAppointments($startDate, $endDate);
        $withCash = $this->importantForTodayRepository->getAppointmentsWithCash($startDate, $endDate);
        
        return response()->json([
            'without_forms' => $withoutForms,
            'with_required_eligibility' => $withRequiredEligibility,
            'with_deductible' => $withDeductible,
            'with_negative_balance' => $withNegativeBalance,
            'patient_last_appointments' => $patientLastAppointments,
            'with_cash' => $withCash,
        ]);
    }

    public function getNewLostPatients(): JsonResponse
    {
        $startDate = Carbon::today()->subMonths(3)->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        $newPatients = $this->newLostPatientsRepository->getNewPatients($startDate, $endDate); 
        $inactivePatients = $this->newLostPatientsRepository->getInactivePatients($startDate, $endDate);
        $lostPatients = $this->newLostPatientsRepository->getLostPatients($startDate, $endDate);

        return response()->json([
            'new_patients' => $newPatients,
            'inactive_patients' => $inactivePatients,
            'lost_patients' => $lostPatients,
        ]);
    }

    public function getTridiuumAppointmentsData(): JsonResponse
    {
        $count = KaiserAppointment::newAppointmentsCount(Carbon::today());
        $isRestartingTridiuumParsers = (bool) optional(Option::getOption('is_restarting_tridiuum_parsers'))->option_value;

        return response()->json([
            'count' => $count,
            'is_restarting_tridiuum_parsers' => $isRestartingTridiuumParsers,
        ]);
    }

    public function restartTridiuumParsers(): JsonResponse
    {
        $isRestartingTridiuumParsers = optional(Option::getOption('is_restarting_tridiuum_parsers'))->option_value;

        if (! $isRestartingTridiuumParsers) {
            Option::setOptionValue('is_restarting_tridiuum_parsers', 1);
            
            dispatch(new RestartTridiuumAppointmentsParserJob());
        }

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}

<?php

namespace App\Http\Controllers\Api\Ringcentral;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ringcentral\RingOut\Update;
use App\Http\Requests\Ringcentral\RingOut\Destroy;
use App\Http\Requests\Ringcentral\RingOut\GetByAppointment;
use App\Http\Requests\Ringcentral\RingOut\StoreForAppointment;
use App\Http\Requests\Ringcentral\RingOut\StoreForPatient;
use App\Http\Requests\Ringcentral\RingOut\StoreExternalLogForPatient;
use App\Http\Requests\Ringcentral\RingOut\GetPatientCallDetails;
use App\Appointment;
use App\Models\RingcentralCallLog;
use App\Repositories\Ringcentral\RingcentralRepositoryInterface;
use App\Services\Ringcentral\RingcentralNumber;

class RingOutController extends Controller
{
    /**
     * @var RingcentralRepositoryInterface
     */
    private $ringcentralRepository;
    
    public function __construct(RingcentralRepositoryInterface $ringcentralRepository)
    {
        $this->ringcentralRepository = $ringcentralRepository;
    }
    
    public function getByAppointment(GetByAppointment $request, Appointment $appointment)
    {
        $appointment->load([
            'patient:id,first_name,last_name,sex,date_of_birth,cell_phone,home_phone,work_phone,subscriber_id,preferred_language_id',
            'patient.preferredLanguage',
            'provider:id,provider_name,phone',
        ]);
        
        return response()->json([
            'appointment' => array_only($appointment->toArray(), [
                'id',
                'time',
                'visit_copay',
                'visit_length',
                'reason_for_visit',
                'patient',
                'provider',
            ]),
            'call_log' => $this->ringcentralRepository->getByAppointment($appointment),
        ]);
    }
    
    public function storeForAppointment(StoreForAppointment $request)
    {
        return response()->json([
            'call_log' => $this->ringcentralRepository->storeAppointmentRingOut($request->phone_from, $request->phone_to, $request->play_prompt, $request->appointment_type, $request->appointment_id),
        ]);
    }

    public function storeForPatient(StoreForPatient $request)
    {
        return response()->json([
            'call_log' => $this->ringcentralRepository->storePatientRingOut($request->phone_from, $request->phone_to, $request->play_prompt, $request->patient_type, $request->patient_id, $request->only_for_admin),
        ]);
    }

    public function storeExternalLogForPatient(StoreExternalLogForPatient $request)
    {
        return response()->json([
            'call_log' => $this->ringcentralRepository->storePatientExternalRingOutLog($request->phone_from, $request->phone_to, $request->patient_type, $request->patient_id, $request->only_for_admin),
        ]);
    }
    
    public function update(Update $request, RingcentralCallLog $callLog)
    {
        return response()->json([
            'call_log' => $this->ringcentralRepository->updateCallLog($callLog, $request->all()),
        ]);
    }
    
    /**
     * @param Destroy            $request
     * @param RingcentralCallLog $callLog
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Destroy $request, RingcentralCallLog $callLog)
    {
        $this->ringcentralRepository->destroyRingOut($callLog);
        
        return response()->json([
            'call_log' => $callLog->refresh(),
        ]);
    }

    public function getAccountNumbers()
    {
        if (config('app.env') !== 'production') {
            return [];
        }

        $ringcentralNumbersService = new RingcentralNumber();
        
        $list = $ringcentralNumbersService->list();
    
        $filteredList = array_where($list, function ($record) {
            return mb_stripos(data_get($record, 'type'), 'voice') !== false && data_get($record, 'usageType') == 'DirectNumber';
        });

        return array_values($filteredList);
    }

    public function getPatientCallDetails(GetPatientCallDetails $request, $patientId)
    {
        $patientType = $request->query('patient_type') ?? 'patient';

        return $this->ringcentralRepository->getPatientCallDetails($patientId, $patientType);
    }
}

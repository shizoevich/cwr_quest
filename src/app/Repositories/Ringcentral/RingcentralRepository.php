<?php

namespace App\Repositories\Ringcentral;

use App\Enums\Ringcentral\RingcentralCallerStatus;
use App\Enums\Ringcentral\RingcentralCallStatus;
use App\Enums\Ringcentral\RingcentralTelephonyStatus;
use App\Events\Ringcentral\RingcentralCallChanged;
use App\Appointment;
use App\KaiserAppointment;
use App\Models\RingcentralCallLog;
use App\Patient;
use App\Models\Patient\Lead\PatientLead;
use App\Services\Ringcentral\RingcentralRingOut;
use App\Contracts\Models\Appointment as AppointmentContract;
use App\Contracts\Models\Patient as PatientContract;
use Carbon\Carbon;

class RingcentralRepository implements RingcentralRepositoryInterface
{
    public function storeAppointmentRingOut(string $phoneFrom, string $phoneTo, bool $playPrompt, string $appointmentType, int $appointmentId)
    {
        $appointment = $this->getAppointment($appointmentType, $appointmentId);
        if (!$appointment) {
            return null;
        }

        $callSubjectType = RingcentralCallLog::SUBJECT_TYPES[$appointmentType] ?? null;
        if (!$callSubjectType) {
            return null;
        }

        $data = [
            'patient_id' => $appointment->getPatientId(),
            'appointment_id' => $appointmentId,
            'appointment_type' => $appointmentType,
            'call_subject_id' => $appointmentId,
            'call_subject_type' => $callSubjectType,
        ];

        return $this->storeRingOut($phoneFrom, $phoneTo, $playPrompt, $data);
    }

    /**
     * @param string $appointmentType
     * @param int    $appointmentId
     *
     * @return AppointmentContract|null
     */
    private function getAppointment(string $appointmentType, int $appointmentId)
    {
        if ($appointmentType === 'appointment') {
            return Appointment::find($appointmentId);
        }
        if ($appointmentType === 'tridiuum_appointment') {
            return KaiserAppointment::find($appointmentId);
        }
        
        return null;
    }

    public function storePatientRingOut(string $phoneFrom, string $phoneTo, bool $playPrompt, string $patientType, int $patientId, ?bool $onlyForAdmin = false)
    {
        $patient = $this->getPatient($patientType, $patientId);
        if (!$patient) {
            return null;
        }

        $callSubjectType = RingcentralCallLog::SUBJECT_TYPES[$patientType] ?? null;
        if (!$callSubjectType) {
            return null;
        }

        $data = [
            'patient_id' => $patientId,
            'call_subject_id' => $patientId,
            'call_subject_type' => $callSubjectType,
            'only_for_admin' => $onlyForAdmin ?? false,
        ];

        return $this->storeRingOut($phoneFrom, $phoneTo, $playPrompt, $data);
    }

    /**
     * @param string $patientType
     * @param int    $patientId
     *
     * @return PatientContract|null
     */
    private function getPatient(string $patientType, int $patientId)
    {
        if ($patientType === 'patient') {
            return Patient::find($patientId);
        }
        if ($patientType === 'patient_lead') {
            return PatientLead::find($patientId);
        }
        
        return null;
    }

    private function storeRingOut(string $phoneFrom, string $phoneTo, bool $playPrompt, array $data)
    {
        $ringOut = new RingcentralRingOut();
        $call = $ringOut->store($phoneFrom, $phoneTo, $playPrompt);
    
        return RingcentralCallLog::create(array_merge($data, [
            'user_id' => auth()->user()->id,
            'ring_central_session_id' => $call['id'],
            'telephony_status' => RingcentralTelephonyStatus::STATUS_RINGING,
            'call_status' => RingcentralCallStatus::MAPPED_STATUSES[$call['status']['callStatus']],
            'caller_status' => RingcentralCallerStatus::MAPPED_STATUSES[$call['status']['callerStatus']],
            'callee_status' => RingcentralCallerStatus::MAPPED_STATUSES[$call['status']['calleeStatus']],
            'call_starts_at' => Carbon::now(),
        ]));
    }
    
    public function updateCallLog(RingcentralCallLog $callLog, array $data): RingcentralCallLog
    {
        $callLog->update([
            'comment' => data_get($data, 'comment'),
        ]);
        
        return $callLog;
    }
    
    public function destroyRingOut(RingcentralCallLog $callLog)
    {
        $ringcentralRingOut = new RingcentralRingOut();
        $deleted = $ringcentralRingOut->delete($callLog->ring_central_session_id);
        if ($deleted) {
            $callLog->update([
                'telephony_status' => RingcentralTelephonyStatus::STATUS_NO_CALL,
                'call_ends_at' => Carbon::now(),
            ]);
            event(new RingcentralCallChanged($callLog));
        }
        
        return $deleted;
    }
    
    public function getByAppointment(Appointment $appointment)
    {
        return $appointment->ringcentralCallLogs()->latest()->first();
    }

    public function getPatientCallDetails($patientId, $patientType)
    {
        $patient = $this->getPatient($patientType, $patientId);
        if (!$patient) {
            return null;
        }

        $patient->load([
            'lastFiveRingcentralCallLogs' => function($query) {
                $query
                    ->with(['user.meta', 'user.provider'])
                    ->when(!auth()->user()->isAdmin(), function($subQuery) {
                        $subQuery->where('ringcentral_call_logs.only_for_admin', '=', 0);
                    });
            },
        ]);
        $patient->ringcentral_call_logs = $patient->lastFiveRingcentralCallLogs;

        $inquiry = $patient->inquiries()->first();
        if (isset($inquiry)) {
            $patient->onboarding_phone = $inquiry->onboarding_phone;
        }

        if ($patientType === 'patient') {
            $patientLead = $patient->patientLead()->withTrashed()->first();
            if (isset($patientLead)) {
                $patientLead->load(['lastFiveRingcentralCallLogs.user.meta']);
                $mergedLogs = $patient->lastFiveRingcentralCallLogs->merge($patientLead->lastFiveRingcentralCallLogs)
                    ->sortByDesc('id')
                    ->take(5);
                $patient->ringcentral_call_logs = $mergedLogs;
            }
        }

        return $patient;
    }

    public function storePatientExternalRingOutLog(string $phoneFrom, string $phoneTo, string $patientType, int $patientId, ?bool $onlyForAdmin = false)
    {
        $patient = $this->getPatient($patientType, $patientId);
        if (!$patient) {
            return null;
        }

        $callSubjectType = RingcentralCallLog::SUBJECT_TYPES[$patientType] ?? null;
        if (!$callSubjectType) {
            return null;
        }

        $data = [
            'patient_id' => $patientId,
            'call_subject_id' => $patientId,
            'call_subject_type' => $callSubjectType,
            'only_for_admin' => $onlyForAdmin ?? false,
        ];

        return $this->storeExternalRingOutLog($phoneFrom, $phoneTo, $data);
    }

    private function storeExternalRingOutLog(string $phoneFrom, string $phoneTo, array $data)
    {
        return RingcentralCallLog::create(array_merge($data, [
            'user_id' => auth()->user()->id,
            'ring_central_session_id' => 'custom-' . md5(uniqid() . time()),
            'telephony_status' => RingcentralTelephonyStatus::STATUS_NO_CALL,
            'phone_from' => $phoneFrom,
            'phone_to' => $phoneTo,
            'call_status' => RingcentralCallStatus::STATUS_SUCCESS,
            'caller_status' => RingcentralCallerStatus::STATUS_SUCCESS,
            'callee_status' => RingcentralCallerStatus::STATUS_SUCCESS,
            'call_starts_at' => Carbon::now(),
        ]));
    }
}
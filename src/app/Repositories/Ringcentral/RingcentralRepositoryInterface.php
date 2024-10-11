<?php

namespace App\Repositories\Ringcentral;

use App\Appointment;
use App\Models\RingcentralCallLog;

interface RingcentralRepositoryInterface
{
    /**
     * @param string $phoneFrom
     * @param string $phoneTo
     * @param bool   $playPrompt
     * @param string $appointmentType
     * @param int    $appointmentId
     *
     * @return RingcentralCallLog|null
     */
    public function storeAppointmentRingOut(string $phoneFrom, string $phoneTo, bool $playPrompt, string $appointmentType, int $appointmentId);

    /**
     * @param string $phoneFrom
     * @param string $phoneTo
     * @param bool   $playPrompt
     * @param string $patientType
     * @param int    $patientId
     *
     * @return RingcentralCallLog|null
     */
    public function storePatientRingOut(string $phoneFrom, string $phoneTo, bool $playPrompt, string $patientType, int $patientId);
    
    /**
     * @param RingcentralCallLog $callLog
     * @param array              $data
     *
     * @return RingcentralCallLog
     */
    public function updateCallLog(RingcentralCallLog $callLog, array $data): RingcentralCallLog;
    
    /**
     * @param RingcentralCallLog $callLog
     *
     * @return mixed
     */
    public function destroyRingOut(RingcentralCallLog $callLog);
    
    /**
     * @param Appointment $appointment
     *
     * @return RingcentralCallLog|null
     */
    public function getByAppointment(Appointment $appointment);

    /**
     * @param int    $patientId
     * @param string $patientType
     *
     * @return mixed
     */
    public function getPatientCallDetails($patientId, $patientType);

    /**
     * @param string $phoneFrom
     * @param string $phoneTo
     * @param bool   $playPrompt
     * @param string $patientType
     * @param int    $patientId
     *
     * @return RingcentralCallLog|null
     */
    public function storePatientExternalRingOutLog(string $phoneFrom, string $phoneTo, string $patientType, int $patientId);
}
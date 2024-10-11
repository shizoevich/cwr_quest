<?php

namespace App\Repositories\Appointment\Model;

use App\Appointment;
use Illuminate\Database\Eloquent\Collection;

interface AppointmentRepositoryInterface
{
    /**
     * @param array $data
     * @return Collection
     */
    public function all(array $data): Collection;

    /**
     * @param \App\Appointment $appointment
     * @return mixed
     */
    public function delete(\App\Appointment $appointment): bool;

    /**
     * @param array $attributes
     * @return Appointment|null
     */
    public function create(array $attributes): ?Appointment;

    /**
     * @param array $attributes
     * @param Appointment $appointment
     * @return Appointment
     */
    public function update(array $attributes, Appointment $appointment): Appointment;

    /**
     * @param array $attributes
     * @param Appointment $appointment
     * @return Appointment
     */
    public function rescheduleAppointment(array $attributes, Appointment $appointment): Appointment;
    
    /**
     * @return array
     */
    public function getAvailableStatuses(): array;
    
    /**
     * @param Appointment $appointment
     *
     * @return bool
     */
    public function isProgressNoteMissing(Appointment $appointment): bool;
    
    public function visitCorrectionData(Appointment $appointment, string $insuranceName, string $oldPos, string $oldCpt, string $oldModifierA, bool $allowChangeCpt = true, bool $allowChangePos = true, bool $allowChangeModifierA = true);
    
    public function visitCorrectionDataByAppointment(Appointment $appointment, bool $allowChangeCpt = true, bool $allowChangePos = true, bool $allowChangeModifierA = true);
    
    public function importantDirties(array $ids);

    public function createVisitForAppointment(Appointment $appointment);

    public function createLateCancellationTransaction(Appointment $appointment, $chargeForCancellation);
}
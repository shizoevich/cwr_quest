<?php

namespace App\Console\Commands\KaiserAudit;

use App\Appointment;
use App\Http\Controllers\PatientTrait;
use Carbon\Carbon;
use InvalidArgumentException;

trait AppointmentAvailabilityTrait
{
    use PatientTrait;

    /**
     * @param Appointment $appointment
     * @return Carbon[]
     */
    private function getFreeTimeAroundAppointment(Appointment $appointment): array
    {
        $fromAppointment = Appointment::query()
            ->select(['id', 'time', 'visit_length'])
            ->selectRaw('from_unixtime(time) as start_at')
            ->where('providers_id', $appointment->providers_id)
            ->where('time', '<', $appointment->time)
            ->orderByDesc('time')
            ->first();
        $toAppointment = Appointment::query()
            ->select(['id', 'time', 'visit_length'])
            ->selectRaw('from_unixtime(time) as start_at')
            ->where('providers_id', $appointment->providers_id)
            ->where('time', '>', $appointment->time)
            ->orderBy('time')
            ->first();

        $from = null;
        if ($fromAppointment) {
            $from = Carbon::parse($fromAppointment->start_at)->addMinutes($fromAppointment->visit_length);
            $visitEndTime = $this->getVisitEndTimeFromLogs($fromAppointment);
            if ($visitEndTime) {
                $from = Carbon::parse($visitEndTime);
            }
        }

        $to = null;
        if ($toAppointment) {
            $to = Carbon::parse($toAppointment->start_at);
            $visitStartTime = $this->getVisitStartTimeFromLogs($toAppointment);
            if ($visitStartTime) {
                $to = Carbon::parse($visitStartTime);
            }
        }

        return [$from, $to];
    }

    /**
     * @param Appointment $appointment
     * @return Carbon[]
     * @throws \Random\RandomException
     */
    private function findAppointmentStartAndEndTime(Appointment $appointment): array
    {
        $appointmentStartsAt = Carbon::parse($appointment->start_at);
        $appointmentEndsAt = $appointmentStartsAt->copy()->addMinutes($appointment->visit_length);

        [$freeFrom, $freeTo] = $this->getFreeTimeAroundAppointment($appointment);
        $useFreeFrom = false;
        $useFreeTo = false;
        if ($freeFrom && $freeFrom->gt($appointmentStartsAt)) {
            $appointmentStartsAt = $freeFrom->copy();
            $useFreeFrom = true;
        }
        if ($freeTo && $freeTo->lt($appointmentEndsAt)) {
            $appointmentEndsAt = $freeTo->copy();
            $useFreeTo = true;
        }
        if ($appointmentEndsAt->diffInSeconds($appointmentStartsAt) < $this->minSessionLength()) {
            if ($useFreeFrom && $useFreeTo) {
                throw new InvalidArgumentException(
                    "Too little available time for the appointment: $appointment->id, free time from {$freeFrom->toDateTimeString()} to {$freeTo->toDateTimeString()}"
                );
            }
            if ($useFreeFrom) {
                if ($freeTo) {
                    if ($freeTo->diffInSeconds($appointmentStartsAt) < $this->minSessionLength()) {
                        throw new InvalidArgumentException(
                            "Too little available time for the appointment: $appointment->id, free time from {$appointmentStartsAt->toDateTimeString()} to {$freeTo->toDateTimeString()}"
                        );
                    }
                    $duration = min($appointment->visit_length * Carbon::SECONDS_PER_MINUTE, $freeTo->diffInSeconds($appointmentStartsAt));
                } else {
                    $duration = $appointment->visit_length * Carbon::SECONDS_PER_MINUTE;
                }

                $appointmentEndsAt = $appointmentStartsAt->copy()->addSeconds($duration);
            }
            if ($useFreeTo) {
                if ($freeFrom) {
                    if ($appointmentEndsAt->diffInSeconds($freeFrom) < $this->minSessionLength()) {
                        throw new InvalidArgumentException(
                            "Too little available time for the appointment: $appointment->id, free time from {$freeFrom->toDateTimeString()} to {$appointmentEndsAt->toDateTimeString()}"
                        );
                    }
                    $duration = min($appointment->visit_length * Carbon::SECONDS_PER_MINUTE, $appointmentEndsAt->diffInSeconds($freeFrom));
                } else {
                    $duration = $appointment->visit_length * Carbon::SECONDS_PER_MINUTE;
                }

                $appointmentStartsAt = $appointmentEndsAt->copy()->subSecond($duration);
            }
        }

        return [$appointmentStartsAt->copy(), $appointmentEndsAt->copy()];
    }

    abstract protected function minSessionLength(): int;
}
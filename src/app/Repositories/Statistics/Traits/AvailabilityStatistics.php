<?php

namespace App\Repositories\Statistics\Traits;

use Illuminate\Support\Facades\DB;
use App\Helpers\AvailabilityHelper;
use App\Availability;
use App\Appointment;
use App\Status;
use Carbon\Carbon;

trait AvailabilityStatistics
{
    protected function getNearEventDate(Availability $event, Carbon $startDate, Carbon $endDate)
    {
        $startDate = Carbon::parse($event->start_date);

        $startDateDayOfWeek = AvailabilityHelper::getDayOfWeek($startDate->dayOfWeek);
        if ($startDateDayOfWeek == $event->day_of_week) {
            return $startDate->setTimeFromTimeString($event->start_time);
        }

        if ($event->day_of_week < $startDateDayOfWeek) {
            $days = 7 - $startDateDayOfWeek + $event->day_of_week;
        } else {
            $days = $event->day_of_week - $startDateDayOfWeek;
        }
        $startDate->addDays($days)->setTimeFromTimeString($event->start_time);

        if ($startDate->gt($endDate) || $startDate->lt($startDate)) {
            return null;
        }

        return $startDate;
    }

    public function getAvailabilities($startDate, $endDate)
    {
        if (!($startDate instanceof Carbon)) {
            $startDate = Carbon::parse($startDate);
        }
        if (!($endDate instanceof Carbon)) {
            $endDate = Carbon::parse($endDate);
        }

        $data = Availability::query()
            ->select('availabilities.*')
            ->join('providers', 'providers.id', '=', 'availabilities.provider_id')
            ->where('providers.is_test', '=', 0)
            ->whereNotNull('availabilities.start_date')
            ->whereDate('availabilities.start_date', '>=', $startDate->toDateString())
            ->whereDate('availabilities.start_date', '<', $endDate->toDateString())
            ->get();

        return $data->reduce(function ($carry, $item) use (&$startDate, &$endDate) {
            $eventNearDate = $this->getNearEventDate($item, $startDate, $endDate);

            if (isset($eventNearDate)) {
                $item->t_date = $eventNearDate;
                $carry->push($item);
            }
            
            return $carry;
        }, collect([]));
    }

    public function getAvailabilitiesMapping($startDate, $endDate)
    {
        $availabilities = $this->getAvailabilities($startDate, $endDate);
        if (is_null($availabilities)) {
            return [];
        }

        return $availabilities->reduce(function ($carry, $item) {
            if (!isset($carry[$item->provider_id])) {
                $carry[$item->provider_id] = [];
            }

            array_push($carry[$item->provider_id], $item);
            
            return $carry;
        }, []);
    }

    public function getAvailabilitiesStatisticsMapping($startDate, $endDate)
    {
        $availabilitiesMapping = $this->getAvailabilitiesMapping($startDate, $endDate);
        $availabilitiesStatisticsMapping = [];

        foreach ($availabilitiesMapping as $key => $value) {
            $initialAvailabilityLength = 0;
            $remainingAvailabilityLength = 0;

            foreach ($value as $availability) {
                $initialAvailabilityLength += ($availability->length ?? 0);
                $remainingAvailabilityLength += ($availability->remaining_length ?? 0);
            }
            
            $availabilitiesStatisticsMapping[$key] = [
                'initial_availability_length' => $initialAvailabilityLength / 60,
                'remaining_availability_length' => $remainingAvailabilityLength / 60,
            ];
        }

        return $availabilitiesStatisticsMapping;
    }

    public function getAppointments($startDate, $endDate)
    {
        if (!($startDate instanceof Carbon)) {
            $startDate = Carbon::parse($startDate);
        }
        if (!($endDate instanceof Carbon)) {
            $endDate = Carbon::parse($endDate);
        }

        return Appointment::query()
            ->select(['appointments.*', 'patients.primary_insurance', DB::raw('DATE(FROM_UNIXTIME(`appointments`.`time`)) AS appt_date')])
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->join('providers', 'providers.id', '=', 'appointments.providers_id')
            ->where('patients.is_test', '=', 0)
            ->where('providers.is_test', '=', 0)
            ->havingRaw("appt_date >= DATE('{$startDate->toDateString()}')")
            ->havingRaw("appt_date <= DATE('{$endDate->toDateString()}')")
            ->get();
    }

    public function getAppointmentsMapping($startDate, $endDate)
    {
        $appointments = $this->getAppointments($startDate, $endDate);
        if (is_null($appointments)) {
            return [];
        }

        return $appointments->reduce(function ($carry, $item) {
            if (!isset($carry[$item->providers_id])) {
                $carry[$item->providers_id] = [];
            }

            array_push($carry[$item->providers_id], $item);
            
            return $carry;
        }, []);
    }

    public function getAppointmentsStatisticsMapping($startDate, $endDate)
    {
        $activeStatusId = Status::getActiveId();
        $completedStatusId = Status::getCompletedId();
        $visitCreatedStatusId = Status::getVisitCreatedId();
        $cancelStatusesIds = Status::getNewCancelStatusesId();
        $rescheduleStatusesIds = Status::getRescheduleStatusesId();
        $cancelledByPatientStatusId = Status::getCancelledByPatientId();
        $cancelledByProviderStatusId = Status::getCancelledByProviderId();
        $lastMinuteCancelByPatientStatusId = Status::getLastMinuteCancelByPatientId();
        $patientDidNotComeStatusId = Status::getPatientDidNotComeId();
        $cancelledByOfficeStatusId = Status::getCancelledByOfficeId();

        $appointmentsMapping = $this->getAppointmentsMapping($startDate, $endDate);
        $appointmentsStatisticsMapping = [];

        foreach ($appointmentsMapping as $key => $value) {
            $appointmentsLength = 0;
            $activeAppointmentsCount = 0;
            $activeAppointmentsLength = 0;
            $completedAppointmentsCount = 0;
            $completedAppointmentsLength = 0;
            $visitCreatedAppointmentsCount = 0;
            $visitCreatedAppointmentsLength = 0;
            $cancelledAppointmentsCount = 0;
            $cancelledAppointmentsLength = 0;
            $rescheduledAppointmentsCount = 0;
            $rescheduledAppointmentsLength = 0;
            $kaiserAppointmentsCount = 0;

            $cancelledByPatientAppointmentsCount = 0;
            $cancelledByProviderAppointmentsCount = 0;
            $lastMinuteCancelByPatientAppointmentsCount = 0;
            $patientDidNotComeAppointmentsCount = 0;
            $cancelledByOfficeAppointmentsCount = 0;

            foreach ($value as $appointment) {
                $appointmentsLength += ($appointment->visit_length ?? 0);

                if (isset($appointment->primary_insurance) && $this->isKaiserInsurance($appointment->primary_insurance)) {
                    $kaiserAppointmentsCount += 1;
                }

                if (!isset($appointment->appointment_statuses_id)) {
                    continue;
                }

                if ($appointment->appointment_statuses_id == $activeStatusId) {
                    $activeAppointmentsCount += 1;
                    $activeAppointmentsLength += ($appointment->visit_length ?? 0);
                } elseif ($appointment->appointment_statuses_id == $completedStatusId) {
                    $completedAppointmentsCount += 1;
                    $completedAppointmentsLength += ($appointment->visit_length ?? 0);
                } elseif ($appointment->appointment_statuses_id == $visitCreatedStatusId) {
                    $visitCreatedAppointmentsCount += 1;
                    $visitCreatedAppointmentsLength += ($appointment->visit_length ?? 0);
                } elseif (in_array($appointment->appointment_statuses_id, $rescheduleStatusesIds)) {
                    $rescheduledAppointmentsCount += 1;
                    $rescheduledAppointmentsLength += ($appointment->visit_length ?? 0);
                } elseif (in_array($appointment->appointment_statuses_id, $cancelStatusesIds)) {
                    $cancelledAppointmentsCount += 1;
                    $cancelledAppointmentsLength += ($appointment->visit_length ?? 0);

                    switch ($appointment->appointment_statuses_id) {
                        case $cancelledByPatientStatusId:
                            $cancelledByPatientAppointmentsCount += 1;
                            break;
                        case $cancelledByProviderStatusId:
                            $cancelledByProviderAppointmentsCount += 1;
                            break;
                        case $lastMinuteCancelByPatientStatusId:
                            $lastMinuteCancelByPatientAppointmentsCount += 1;
                            break;
                        case $patientDidNotComeStatusId:
                            $patientDidNotComeAppointmentsCount += 1;
                            break;
                        case $cancelledByOfficeStatusId:
                            $cancelledByOfficeAppointmentsCount += 1;
                            break;
                    }
                }
            }

            // @todo remove
            // $patientsCount = collect($value)->unique('patients_id')->values()->count();
            // $patientsWithVisitsCount = collect($value)->filter(function ($appt) use ($visitCreatedStatusId) {
            //     return $appt->appointment_statuses_id == $visitCreatedStatusId;
            // })->unique('patients_id')->values()->count();

            $appointmentsStatisticsMapping[$key] = [
                'appointments_count' => count($value),
                'appointments_length' => $appointmentsLength / 60,
                'kaiser_appointments_count' => $kaiserAppointmentsCount,
                'active_appointments_count' => $activeAppointmentsCount,
                'active_appointments_length' => $activeAppointmentsLength / 60,
                'completed_appointments_count' => $completedAppointmentsCount,
                'completed_appointments_length' => $completedAppointmentsLength / 60,
                'visit_created_appointments_count' => $visitCreatedAppointmentsCount,
                'visit_created_appointments_length' => $visitCreatedAppointmentsLength / 60,
                'cancelled_appointments_count' => $cancelledAppointmentsCount,
                'cancelled_appointments_length' => $cancelledAppointmentsLength / 60,
                'rescheduled_appointments_count' => $rescheduledAppointmentsCount,
                'rescheduled_appointments_length' => $rescheduledAppointmentsLength / 60,
                'cancelled_by_patient_appointments_count' => $cancelledByPatientAppointmentsCount,
                'cancelled_by_provider_appointments_count' => $cancelledByProviderAppointmentsCount,
                'last_minute_cancel_by_patient_appointments_count' => $lastMinuteCancelByPatientAppointmentsCount,
                'patient_did_not_come_appointments_count' => $patientDidNotComeAppointmentsCount,
                'cancelled_by_office_appointments_count' => $cancelledByOfficeAppointmentsCount,
                'cancelled_appointments_rate' => $cancelledAppointmentsCount / count($value),
                // @todo remove
                // 'patients_count' => $patientsCount,
                // 'patients_with_visits_count' => $patientsWithVisitsCount,
            ];
        }

        return $appointmentsStatisticsMapping;
    }

    protected function isKaiserInsurance($insuranceName)
    {
        if (strpos(strtolower($insuranceName), 'kaiser') !== false) {
            return true;
        }

        return strpos(strtolower($insuranceName), 'kaizer') !== false;
    }

    protected function getWeeklyStatistics($startDate, $endDate)
    {
        if (!($startDate instanceof Carbon)) {
            $startDate = Carbon::parse($startDate);
        }
        if (!($endDate instanceof Carbon)) {
            $endDate = Carbon::parse($endDate);
        }

        $weeks = AvailabilityHelper::getWeeks($startDate, $endDate);
        
        return array_map(function ($week) {
            return array_merge($week, [
                'availabilityStatistics' => $this->getAvailabilitiesStatisticsMapping($week['from'], $week['to']),
                'appointmentsStatistics' => $this->getAppointmentsStatisticsMapping($week['from'], $week['to']),
            ]);
        }, $weeks);
    }

    // method is not used anymore
    protected function getProviderAvgWeeklyStatistics($providerId, $weeklyStatistics)
    {
        $initialAvailabilityLengthSum = 0;
        $remainingAvailabilityLengthSum = 0;
        $appointmentsCountSum = 0;
        $appointmentsLengthSum = 0;
        $activeAppointmentsCountSum = 0;
        $activeAppointmentsLengthSum = 0;
        $completedAppointmentsCountSum = 0;
        $completedAppointmentsLengthSum = 0;
        $visitCreatedAppointmentsCountSum = 0;
        $visitCreatedAppointmentsLengthSum = 0;
        $cancelledAppointmentsCountSum = 0;
        $cancelledAppointmentsLengthSum = 0;
        $cancelledAppointmentsRateSum = 0;

        foreach ($weeklyStatistics as $week) {
            $availabilityStatistics = $week['availabilityStatistics'][$providerId] ?? [];
            $appointmentsStatistics = $week['appointmentsStatistics'][$providerId] ?? [];

            $initialAvailabilityLengthSum += $availabilityStatistics['initial_availability_length'] ?? 0;
            $remainingAvailabilityLengthSum += $availabilityStatistics['remaining_availability_length'] ?? 0;
            $appointmentsCountSum += $appointmentsStatistics['appointments_count'] ?? 0;
            $appointmentsLengthSum += $appointmentsStatistics['appointments_length'] ?? 0;
            $activeAppointmentsCountSum += $appointmentsStatistics['active_appointments_count'] ?? 0;
            $activeAppointmentsLengthSum += $appointmentsStatistics['active_appointments_length'] ?? 0;
            $completedAppointmentsCountSum += $appointmentsStatistics['completed_appointments_count'] ?? 0;
            $completedAppointmentsLengthSum += $appointmentsStatistics['completed_appointments_length'] ?? 0;
            $visitCreatedAppointmentsCountSum += $appointmentsStatistics['visit_created_appointments_count'] ?? 0;
            $visitCreatedAppointmentsLengthSum += $appointmentsStatistics['visit_created_appointments_length'] ?? 0;
            $cancelledAppointmentsCountSum += $appointmentsStatistics['cancelled_appointments_count'] ?? 0;
            $cancelledAppointmentsLengthSum += $appointmentsStatistics['cancelled_appointments_length'] ?? 0;
            $cancelledAppointmentsRateSum += $appointmentsStatistics['cancelled_appointments_rate'] ?? 0;
        }

        return [
            'avg_initial_availability_length' => $initialAvailabilityLengthSum / count($weeklyStatistics),
            'avg_remaining_availability_length' => $remainingAvailabilityLengthSum / count($weeklyStatistics),
            'avg_appointments_count' => $appointmentsCountSum / count($weeklyStatistics),
            'avg_appointments_length' => $appointmentsLengthSum / count($weeklyStatistics),
            'avg_active_appointments_count' => $activeAppointmentsCountSum / count($weeklyStatistics),
            'avg_active_appointments_length' => $activeAppointmentsLengthSum / count($weeklyStatistics),
            'avg_completed_appointments_count' => $completedAppointmentsCountSum / count($weeklyStatistics),
            'avg_completed_appointments_length' => $completedAppointmentsLengthSum / count($weeklyStatistics),
            'avg_visit_created_appointments_count' => $visitCreatedAppointmentsCountSum / count($weeklyStatistics),
            'avg_visit_created_appointments_length' => $visitCreatedAppointmentsLengthSum / count($weeklyStatistics),
            'avg_cancelled_appointments_count' => $cancelledAppointmentsCountSum / count($weeklyStatistics),
            'avg_cancelled_appointments_length' => $cancelledAppointmentsLengthSum / count($weeklyStatistics),
            'avg_cancelled_appointments_rate' => $cancelledAppointmentsRateSum / count($weeklyStatistics),
        ];
    }
}

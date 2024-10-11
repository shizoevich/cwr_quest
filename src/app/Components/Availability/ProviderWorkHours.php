<?php

namespace App\Components\Availability;

use App\AvailabilitySubtype;
use App\Helpers\AvailabilityHelper;
use App\Availability;
use App\Appointment;
use App\Status;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProviderWorkHours
{
    /**
     * @var
     */
    protected $startDate;

    /**
     * @var
     */
    protected $endDate;

    /**
     * @var bool
     */
    protected $withActiveAppointments;

    /**
     * @var bool
     */
    private $withCanceledAppointments;

    /**
     * @var bool
     */
    private $allProviders;

    /**
     * @var int|null
     */
    private $providerId;

    /**
     * @var bool
     */
    private $withUniqueTime;

    /**
     * @param          $startDate
     * @param          $endDate
     * @param bool     $withActiveAppointments
     * @param bool     $withCanceledAppointments
     * @param bool     $allProviders
     * @param int|null $providerId
     */
    public function __construct($startDate, $endDate, $withActiveAppointments = true, $withCanceledAppointments = false, $allProviders = false, $providerId = null, $withUniqueTime = false)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->withActiveAppointments = $withActiveAppointments;
        $this->withCanceledAppointments = $withCanceledAppointments;
        $this->allProviders = $allProviders;
        if ($providerId) {
            $this->providerId = $providerId;
        } else {
            $this->providerId = Auth::check() ? Auth::user()->provider_id : null;
        }
        $this->withUniqueTime = $withUniqueTime;
    }

    /**
     * @param $event
     *
     * @return Carbon|null|static
     */
    protected function getNearEventDate($event)
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

        if ($startDate->gt($this->endDate) || $startDate->lt($this->startDate)) {
            return null;
        }

        return $startDate;
    }

    public function getWorkHours()
    {
        $startDate = $this->startDate;
        $endDate = $this->endDate;
        $data = Availability::query()
            ->when($startDate instanceof Carbon, function ($query) use (&$startDate) {
                $query->where(function ($query) use (&$startDate) {
                    $query->orWhereDate('start_date', '>=', $startDate->toDateString());
                });
            })
            ->when($endDate instanceof Carbon, function ($query) use (&$endDate) {
                $query->whereDate('start_date', '<=', $endDate->toDateString());
            })
            ->when(!$this->allProviders, function ($query) {
                $query->where('provider_id', $this->providerId);
            })
            ->whereNotNull('start_date')
            ->whereNull('deleted_at')
            ->get();

        return $data->reduce(function ($carry, $item) {
            $eventNearDate = $this->getNearEventDate($item);

            if (isset($eventNearDate)) {
                $item->t_date = $eventNearDate;
                $carry->push($item);
            }
            
            return $carry;
        }, collect([]));
    }

    public function getWorkHoursMapping()
    {
        $events = $this->getWorkHours();
        if (is_null($events)) {
            return [];
        }

        return $events->reduce(function ($carry, $item) {
            if (!isset($item->provider_id)) {
                return $carry;
            }

            if (!isset($carry[$item->provider_id])) {
                $carry[$item->provider_id] = [];
            }

            array_push($carry[$item->provider_id], $item);
            
            return $carry;
        }, []);
    }

    public function getAppointments()
    {
        $id = (int) $this->providerId;

        $cancelStatusesIds = Status::getOtherCancelStatusesId();
        $activeAndFinishedStatusesIds = Status::getActiveCompletedVisitCreatedStatusesId();
        $cancelStatusesIdsString = implode(',', $cancelStatusesIds);
        $activeAndFinishedStatusesIdsString = implode(',', $activeAndFinishedStatusesIds);

        // $activeOrFinishedAppointmentIdAtTheSameTimeQuery = "
        //     SELECT appt.id 
        //     FROM appointments appt 
        //     WHERE appt.providers_id=appointments.providers_id 
        //         AND (time BETWEEN {$this->startDate->timestamp} AND {$this->endDate->timestamp})
        //         AND ((appt.time BETWEEN appointments.time AND ((appointments.time + appointments.visit_length * 60) - 1)) OR ((appt.time + appt.visit_length * 60) BETWEEN (appointments.time + 1) AND (appointments.time + appointments.visit_length * 60)))
        //         AND appt.appointment_statuses_id IN ($activeAndFinishedStatusesIdsString) 
        //         AND appt.deleted_at IS NULL 
        //     LIMIT 1
        // ";
        // $lastCancelledAppointmentIdAtTheSameTimeQuery = "
        //     SELECT appt.id 
        //     FROM appointments appt 
        //     WHERE appt.providers_id=appointments.providers_id 
        //         AND (time BETWEEN {$this->startDate->timestamp} AND {$this->endDate->timestamp})
        //         AND ((appt.time BETWEEN appointments.time AND ((appointments.time + appointments.visit_length * 60) - 1)) OR ((appt.time + appt.visit_length * 60) BETWEEN (appointments.time + 1) AND (appointments.time + appointments.visit_length * 60)))
        //         AND appt.appointment_statuses_id IN ($cancelStatusesIdsString) 
        //         AND appt.deleted_at IS NULL 
        //     ORDER BY appt.updated_at DESC 
        //     LIMIT 1
        // ";
        $activeOrFinishedAppointmentIdAtTheSameTimeQuery = "SELECT appt.id FROM appointments appt WHERE appt.time=appointments.time AND appt.providers_id=appointments.providers_id AND appt.appointment_statuses_id IN ($activeAndFinishedStatusesIdsString) AND appt.deleted_at IS NULL LIMIT 1";
        $lastCancelledAppointmentIdAtTheSameTimeQuery = "SELECT appt.id FROM appointments appt WHERE appt.time=appointments.time AND appt.providers_id=appointments.providers_id AND appt.appointment_statuses_id IN ($cancelStatusesIdsString) AND appt.deleted_at IS NULL ORDER BY appt.updated_at DESC LIMIT 1";

        $data = Appointment::query()
            ->select(['*', DB::raw('DATE(FROM_UNIXTIME(`appointments`.`time`)) AS appt_date')])
            ->whereHas('patient')
            ->when(!$this->allProviders, function ($query) use ($id) {
                $query->where('providers_id', $id);
            })
            ->when(!$this->withCanceledAppointments, function($query) use ($cancelStatusesIds) {
                $query->whereNotIn('appointment_statuses_id', $cancelStatusesIds);
            })
            ->when(!$this->withActiveAppointments && $this->withCanceledAppointments, function($query) use ($cancelStatusesIds) {
                $query->whereIn('appointment_statuses_id', $cancelStatusesIds);
            })
            ->when($this->withUniqueTime && $this->withCanceledAppointments, function ($query) use ($cancelStatusesIds, $activeOrFinishedAppointmentIdAtTheSameTimeQuery, $lastCancelledAppointmentIdAtTheSameTimeQuery) {
                $query->where(function ($query) use ($cancelStatusesIds, $activeOrFinishedAppointmentIdAtTheSameTimeQuery, $lastCancelledAppointmentIdAtTheSameTimeQuery) {
                    $query->whereNotIn('appointment_statuses_id', $cancelStatusesIds)
                        ->orWhere(function ($query) use ($activeOrFinishedAppointmentIdAtTheSameTimeQuery, $lastCancelledAppointmentIdAtTheSameTimeQuery) {
                            $query->whereRaw("($activeOrFinishedAppointmentIdAtTheSameTimeQuery) IS NULL")
                                ->whereRaw("($lastCancelledAppointmentIdAtTheSameTimeQuery) = appointments.id");
                        });
                });
            })
            ->havingRaw("appt_date >= DATE('{$this->startDate->toDateString()}')")
            ->havingRaw("appt_date <= DATE('{$this->endDate->toDateString()}')")
            ->get();

        return $data;
    }

    public function getAppointmentsMapping()
    {
        $appointments = $this->getAppointments();
        if (is_null($appointments)) {
            return [];
        }

        return $appointments->reduce(function ($carry, $item) {
            if (!isset($item->providers_id)) {
                return $carry;
            }

            if (!isset($carry[$item->providers_id])) {
                $carry[$item->providers_id] = [];
            }

            array_push($carry[$item->providers_id], $item);
            
            return $carry;
        }, []);
    }

    public function getTotalMapping()
    {
        $activeStatusId = Status::getActiveId();
        $completedStatusId = Status::getCompletedId();
        $visitCreatedStatusId = Status::getVisitCreatedId();
        $cancelStatusesIds = Status::getNewCancelStatusesId();
        $cancelledByPatientStatusId = Status::getCancelledByPatientId();
        $cancelledByProviderStatusId = Status::getCancelledByProviderId();
        $lastMinuteCancelByPatientStatusId = Status::getLastMinuteCancelByPatientId();
        $patientDidNotComeStatusId = Status::getPatientDidNotComeId();
        $cancelledByOfficeStatusId = Status::getCancelledByOfficeId();
        $rescheduleStatusesIds = Status::getRescheduleStatusesId();
        $reschedulingSubtypeId = AvailabilitySubtype::getIdByTypeRescheduling();
        $unavailableSubtypeId = AvailabilitySubtype::getIdByTypeUnavailable();

        $appointmentsMapping = $this->getAppointmentsMapping();
        $workHoursMapping = $this->getWorkHoursMapping();
        $totalMapping = [];

        foreach ($appointmentsMapping as $key => $value) {
            $appointmentsLength = 0;
            $activeAppointments = [];
            $activeAppointmentsLength = 0;
            $completedAppointments = [];
            $completedAppointmentsLength = 0;
            $visitCreatedAppointments = [];
            $visitCreatedAppointmentsLength = 0;
            $rescheduledAppointments = [];
            $rescheduledAppointmentsLength = 0;
            $canceledAppointments = [];
            $canceledAppointmentsLength = 0;
            $cancelledByPatientAppointmentsCount = 0;
            $cancelledByProviderAppointmentsCount = 0;
            $lastMinuteCancelByPatientAppointmentsCount = 0;
            $patientDidNotComeAppointmentsCount = 0;
            $cancelledByOfficeAppointmentsCount = 0;

            foreach ($value as $appointment) {
                $appointmentsLength += ($appointment->visit_length ?? 0);

                if (!isset($appointment->appointment_statuses_id)) {
                    continue;
                }

                if ($appointment->appointment_statuses_id == $activeStatusId) {
                    array_push($activeAppointments, $appointment);
                    $activeAppointmentsLength += ($appointment->visit_length ?? 0);
                } elseif ($appointment->appointment_statuses_id == $completedStatusId) {
                    array_push($completedAppointments, $appointment);
                    $completedAppointmentsLength += ($appointment->visit_length ?? 0);
                } elseif ($appointment->appointment_statuses_id == $visitCreatedStatusId) {
                    array_push($visitCreatedAppointments, $appointment);
                    $visitCreatedAppointmentsLength += ($appointment->visit_length ?? 0);
                } elseif (in_array($appointment->appointment_statuses_id, $cancelStatusesIds)) {
                    array_push($canceledAppointments, $appointment);
                    $canceledAppointmentsLength += ($appointment->visit_length ?? 0);

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

                } elseif (in_array($appointment->appointment_statuses_id, $rescheduleStatusesIds)) {
                    array_push($rescheduledAppointments, $appointment);
                    $rescheduledAppointmentsLength += ($appointment->visit_length ?? 0);
                }
            }

            $totalMapping[$key] = [
                'appointmentsCount' => count($value),
                'appointmentsLength' => $appointmentsLength,
                'activeAppointmentsCount' => count($activeAppointments),
                'activeAppointmentsLength' => $activeAppointmentsLength,
                'completedAppointmentsCount' => count($completedAppointments),
                'completedAppointmentsLength' => $completedAppointmentsLength,
                'visitCreatedAppointmentsCount' => count($visitCreatedAppointments),
                'visitCreatedAppointmentsLength' => $visitCreatedAppointmentsLength,
                'rescheduledAppointmentsCount' => count($rescheduledAppointments),
                'rescheduledAppointmentsLength' => $rescheduledAppointmentsLength,
                'canceledAppointmentsCount' => count($canceledAppointments),
                'canceledAppointmentsLength' => $canceledAppointmentsLength,
                'cancelledByPatientAppointmentsCount' => $cancelledByPatientAppointmentsCount,
                'cancelledByProviderAppointmentsCount' => $cancelledByProviderAppointmentsCount,
                'lastMinuteCancelByPatientAppointmentsCount' => $lastMinuteCancelByPatientAppointmentsCount,
                'patientDidNotComeAppointmentsCount' => $patientDidNotComeAppointmentsCount,
                'cancelledByOfficeAppointmentsCount' => $cancelledByOfficeAppointmentsCount,
            ];
        }

        foreach ($workHoursMapping as $key => $value) {
            $initialAvailabilityLength = 0;
            $remainingAvailabilityLength = 0;
            $forApptsInitialAvailabilityLength = 0;
            $forApptsRemainingAvailabilityLength = 0;
            $reschedulingInitialAvailabilityLength = 0;
            $reschedulingRemainingAvailabilityLength = 0;

            foreach ($value as $availability) {
                $initialAvailabilityLength += ($availability->length ?? 0);
                $remainingAvailabilityLength += ($availability->remaining_length ?? 0);

                if ($availability->availability_subtype_id === $reschedulingSubtypeId) {
                    $reschedulingInitialAvailabilityLength += ($availability->length ?? 0);
                    $reschedulingRemainingAvailabilityLength += ($availability->remaining_length ?? 0);
                } else if (!in_array($availability->availability_subtype_id, [$reschedulingSubtypeId, $unavailableSubtypeId])) {
                    $forApptsInitialAvailabilityLength += ($availability->length ?? 0);
                    $forApptsRemainingAvailabilityLength += ($availability->remaining_length ?? 0);
                }
            }
            
            if (!isset($totalMapping[$key])) {
                $totalMapping[$key] = [];
            }
            
            $totalMapping[$key] = array_merge(
                $totalMapping[$key],
                [
                    'initialAvailabilityLength' => $initialAvailabilityLength,
                    'remainingAvailabilityLength' => $remainingAvailabilityLength,
                    'forApptsInitialAvailabilityLength' => $forApptsInitialAvailabilityLength,
                    'forApptsRemainingAvailabilityLength' => $forApptsRemainingAvailabilityLength,
                    'reschedulingInitialAvailabilityLength' => $reschedulingInitialAvailabilityLength,
                    'reschedulingRemainingAvailabilityLength' => $reschedulingRemainingAvailabilityLength,
                ]
            );
        }

        return $totalMapping;
    }
}

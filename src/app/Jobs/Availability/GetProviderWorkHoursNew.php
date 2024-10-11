<?php

namespace App\Jobs\Availability;

use App\Appointment;
use App\Helpers\AvailabilityHelper;
use App\Availability;
use App\AvailabilitySubtype;
use App\AvailabilityType;
use App\Helpers\ColorHelper;
use App\OfficeRoom;
use App\Status;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GetProviderWorkHoursNew implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
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
    private $withRescheduledAppointments;
    /**
     * @var bool
     */
    private $withRescheduledAppointmentsDate;
    /**
     * @var bool
     */
    private $allProviders;
    /**
     * @var int
     */
    private $providerId;

    /**
     * GetProviderWorkHoursNew constructor.
     *
     * @param          $startDate
     * @param          $endDate
     * @param array    $options
     */
    public function __construct($startDate, $endDate, array $options)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->withActiveAppointments = $options['with_active_appointments'] ?? true;
        $this->withCanceledAppointments = $options['with_canceled_appointments'] ?? false;
        $this->withRescheduledAppointments = $options['with_rescheduled_appointments'] ?? false;
        $this->withRescheduledAppointmentsDate = $options['with_rescheduled_appointments_date'] ?? false;
        $this->allProviders = $options['all_providers'] ?? false;
        $this->providerId = isset($options['provider_id']) ? $options['provider_id'] : Auth::user()->provider_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $events = $this->getWorkHours();
        $dataset = [];
        if (!is_null($events)) {
            foreach ($events as $event) {
                $eventNearDate = $this->getNearEventDate($event);
                if (is_null($eventNearDate)) {
                    continue;
                }

                $event->t_date = $eventNearDate;
                $dataset[] = $this->getWorkHoursDatasetItem($event);
            }
        }
        if ($this->withActiveAppointments || $this->withCanceledAppointments || $this->withRescheduledAppointments) {
            $appointmentsDataset = $this->getAppointmentsDataset();
            $dataset = array_merge($dataset, $appointmentsDataset);
        }

        return $dataset;
    }

    /**
     * @param $event
     *
     * @return bool
     */
    protected function isEventRecursive($event)
    {
        return false;
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

    /**
     * @param $type
     *
     * @return mixed
     */
    protected function getWorkHours()
    {
        $startDate = $this->startDate;
        $endDate = $this->endDate;
        $data = Availability::with([
            'provider',
            'office',
            'officeRoom',
            'availabilitySubtype'
        ])
            ->when($startDate instanceof Carbon, function ($query) use (&$startDate) {
                $query->where(function ($query) use (&$startDate) {
                    $query->orWhereDate('start_date', '>=', $startDate->toDateString());
                });
            })
            ->when($endDate instanceof Carbon, function ($query) use (&$endDate) {
                $query->whereDate('start_date', '<', $endDate->toDateString());
            })
            ->when(!$this->allProviders, function ($query) {
                $query->where('provider_id', $this->providerId);
            })
            ->whereNotNull('start_date')
            ->whereNull('deleted_at')
            ->get();

        return $data;
    }

    /**
     * @param $workHour
     *
     * @return array
     */
    protected function getWorkHoursDatasetItem($workHour)
    {
        if (!($workHour->t_date instanceof Carbon)) {
            $workHour->t_date = Carbon::parse($workHour->t_date);
        }
        $workHour->t_date = $workHour->t_date->setTimeFromTimeString($workHour->start_time);
        $dayAheadFromNow = Carbon::now()->addDay();
        $isEditable = $workHour->t_date->gt($dayAheadFromNow);
        
        $title = isset($workHour->availabilitySubtype) && $workHour->availabilitySubtype->id === AvailabilitySubtype::getIdByTypeUnavailable()
            ? $workHour->availabilitySubtype->type
            : 'Availability';

        if ($workHour->virtual) {
            $title .= '&nbsp; <i class="fa fa-video-camera"></i>';
        }
        if ($workHour->in_person) {
            $title .= '&nbsp; <i class="fa fa-user"></i>';
        }

        $color = $isEditable ? '#67c23a' : ColorHelper::adjustBrightness("#67c23a", .5);
        if ($workHour->availabilityType) {
            $hexColor = $workHour->availabilityType->hex_color;
            if (isset($workHour->availabilitySubtype) && isset($workHour->availabilitySubtype->hex_color)) {
                $hexColor = $workHour->availabilitySubtype->hex_color;
            }
            $color = $isEditable ? $hexColor : ColorHelper::adjustBrightness($hexColor, .5);
        }

        return [
            'allDay' => false,
            'editable' => false,
            'forceEventDuration' => true,
            'id' => $workHour->id,
            'item_source' => $workHour,
            'title' => $title,
            'type' => $isEditable ? 'workTime' : 'oldDays',
            'start' => $workHour->t_date->toIso8601String(),
            'end' => $workHour->t_date->addMinutes($workHour->length)->toIso8601String(),
            'backgroundColor' => $color,
            'borderColor' => $color,
        ];
    }

    /**
     * @return array
     */
    public function getAppointments()
    {
        $id = (int) $this->providerId;

        $cancelStatusesIds = Status::getNewCancelStatusesId();
        $rescheduledStatusesIds = Status::getRescheduleStatusesId();
        $cancelStatusesIdsStr = '(' . implode(',', $cancelStatusesIds) . ')';
        $excludeOfficeRooms = '(' . OfficeRoom::query()->where('name', '=', '')->pluck('id')->implode(',') . ')';

        $data = Appointment::query()
            ->select([
                '*',
            DB::raw('DATE(FROM_UNIXTIME(time)) AS appt_date'),
            ])
            ->with([
                'patient',
                'office',
                'officeRoom'
            ])
            ->whereHas('patient')
            ->where('providers_id', $id)
            ->when(
                $this->withActiveAppointments && $this->withCanceledAppointments && $this->withRescheduledAppointments,
                function ($query) {
                    return $query;
                },
                function ($query) use ($cancelStatusesIds, $rescheduledStatusesIds) {
                    $query->where(function ($query) use ($cancelStatusesIds, $rescheduledStatusesIds) {
                        $query
                            ->when($this->withActiveAppointments, function ($query) use ($cancelStatusesIds, $rescheduledStatusesIds) {
                                $query->orWhereNotIn('appointment_statuses_id', array_merge($cancelStatusesIds, $rescheduledStatusesIds));
                            })
                            ->when($this->withCanceledAppointments, function ($query) use ($cancelStatusesIds) {
                                $query->orWhereIn('appointment_statuses_id', $cancelStatusesIds);
                            })
                            ->when($this->withRescheduledAppointments, function ($query) use ($rescheduledStatusesIds) {
                                $query->orWhereIn('appointment_statuses_id', $rescheduledStatusesIds);
                            });
                    });
                }
            )
            ->havingRaw("appt_date >= DATE('{$this->startDate->toDateString()}')")
            ->havingRaw("appt_date <= DATE('{$this->endDate->toDateString()}')")
            ->get();

        if ($this->withRescheduledAppointmentsDate) {
            $this->loadRescheduledAppointmentsDate($data);
        }

        return $data;
    }

    protected function loadRescheduledAppointmentsDate($appointments)
    {
        $appointmentIds = $appointments->pluck('id');
        $rescheduledAppointmentsMapping = Appointment::query()
            ->select(['rescheduled_appointment_id', 'time', 'visit_length'])
            ->whereIn('rescheduled_appointment_id', $appointmentIds)
            ->get()
            ->keyBy('rescheduled_appointment_id');

        $appointments->each(function ($appointment) use (&$rescheduledAppointmentsMapping) {
            $rescheduledAppointment = $rescheduledAppointmentsMapping->get($appointment->id);

            if (isset($rescheduledAppointment)) {
                $start = Carbon::createFromTimestamp($rescheduledAppointment->time);
                $end = $start->copy()->addMinutes($rescheduledAppointment->visit_length);

                $appointment->rescheduled_appointment_date = [
                    'start' => $start->format('m/d/Y H:i'),
                    'end' => $end->format('m/d/Y H:i'),
                ];
            } else {
                $appointment->rescheduled_appointment_date = [
                    'start' => '',
                    'end' => '',
                ];
            }
        });
    }

    protected function getAppointmentsDataset()
    {
        $cancelStatusesIds = Status::getOtherCancelStatusesId();
        $appointments = $this->getAppointments();
        $dataset = [];
        foreach ($appointments as $appointment) {
            $apptDate = Carbon::createFromTimestamp($appointment->time);
            $color = '#409EFF';
            if (in_array($appointment->appointment_statuses_id, $cancelStatusesIds)) {
                if ($apptDate->copy()->startOfDay()->lt(Carbon::today())) {
                    $color = '#C0C4CC';
                } else {
                    $color = '#909399';
                }
            } else if ($apptDate->copy()->startOfDay()->lt(Carbon::today())) {
                $color = '#A0CFFF';
            }
            $dataset[] = [
                'id' => $appointment->id,
                'title' => $appointment->patient->first_name . ' ' . $appointment->patient->last_name,
                'patient_id' => $appointment->patients_id,
                'allDay' => false,
                'start' => $apptDate->toIso8601String(),
                'end' => $apptDate->addMinutes($appointment->visit_length)->toIso8601String(),
                'forceEventDuration' => true,
                'item_source' => $appointment,
                'editable' => false,
                'type' => !in_array($appointment->appointment_statuses_id, $cancelStatusesIds) ? 'appointment' : 'canceled_appointment',
                'backgroundColor' => $color,
                'borderColor' => $color,
            ];
        }

        return $dataset;
    }
}

<?php

namespace App\Jobs\Availability;

use App\Appointment;
use App\Helpers\AvailabilityHelper;
use App\OfficeRoom;
use App\ProviderWorkHour;
use App\Status;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/*
 * Class is deprecated
 */
class GetProviderWorkHours implements ShouldQueue
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
    protected $withAppointments;
    /**
     * @var bool
     */
    private $allProviders;

    /**
     * Create a new job instance.
     *
     * @param $startDate
     * @param $endDate
     * @param bool $withAppointments
     * @param bool $allProviders
     */
    public function __construct($startDate, $endDate, $withAppointments = true, $allProviders = false, $providerId = false)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->withAppointments = $withAppointments;
        $this->allProviders = $allProviders;
        if($providerId) {
            $this->providerId = $providerId;
        } else {
            $this->providerId = Auth::check() ? Auth::user()->provider_id : null;
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $recursiveEvents = $this->getWorkHours('only_recursive');
        $events = $this->getWorkHours('only_not_recursive');
        $dataset = [];
        foreach ($recursiveEvents as $event) {

            $eventNearDate = $this->getNearRecursiveEventDate($event);

            if (is_null($eventNearDate)) {
                continue;
            }

            $event->t_date = $eventNearDate;

            $deletedEvents = $event->childDeletedEvents()
                ->whereDate('start_date', '<=', $event->t_date->toDateString())
                ->whereDate('end_date', '>=', $event->t_date->toDateString())
                ->pluck('parent_id')
                ->toArray();

            if (!in_array($event->id, $deletedEvents)) {
                $changedEvent = $event->childEvents()
                    ->with([
                        'provider',
                        'office',
                        'officeRoom',
                    ])
                    ->whereDate('start_date', '<=', $event->t_date->toDateString())
                    ->whereDate('end_date', '>=', $event->t_date->toDateString())
                    ->first();
                if (!is_null($changedEvent)) {
                    $eventNearDate = $this->getNearEventDate($changedEvent);
                    if (is_null($eventNearDate)) {
                        continue;
                    }

                    $changedEvent->t_date = $eventNearDate;
                    $event = $changedEvent;
                }
                $dataset[] = $this->getWorkHoursDatasetItem($event);
            }
        }
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
        if ($this->withAppointments) {
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
        return $event->repeat > 0;
    }

    /**
     * @param $event
     *
     * @return null
     */
    protected function getNearRecursiveEventDate($event)
    {
        if (!$this->isEventRecursive($event)) {
            return null;
        }
        $date = clone $event->start_date;
        $date = $date->startOfDay();
        $eDate = null;
        $eventEndDate = is_null($event->end_date) ? null : Carbon::parse($event->end_date)->endOfDay();
        $counter = 0;
        while (true) {
            if ($date->lte($this->endDate)) {
                $counter++;
                $dateDiff = $date->diffInHours($this->endDate);
                if ($dateDiff > $event->repeat * 24) {   //24 - hours per day
                    $date->addDays($event->repeat);
                    continue;
                }
                $eDate = $date->startOfWeek()
                    ->addDays($event->day_of_week)
                    ->setTimeFromTimeString($event->start_time);
                $eventStartDate = clone $event->start_date;
                $eventStartDate = $eventStartDate->startOfDay();
                if ((!is_null($eventEndDate) && $eDate->gt($eventEndDate)) || $eDate->lt($eventStartDate)) {
                    return null;
                }
            }

            return $eDate;
        }
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
    protected function getWorkHours($type = 'all')
    {
        $startDate = $this->startDate;
        $endDate = $this->endDate;
        $data = ProviderWorkHour::with([
            'provider',
            'office',
            'officeRoom',
        ])
            ->when($this->startDate instanceof Carbon, function ($query) use (&$startDate) {
                $query->where(function ($query) use (&$startDate) {
                    $query->whereNull('end_date');
                    $query->orWhereDate('end_date', '>=', $startDate->toDateString());
                });
            })->when($endDate instanceof Carbon, function ($query) use (&$endDate) {
                $query->whereDate('start_date', '<=', $endDate->toDateString());
            })->when($type === 'only_recursive', function ($query) {
                $query->where('repeat', '>', 0);
            })->when($type === 'only_not_recursive', function ($query) {
                $query->where('repeat', '=', 0);
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
        $now = Carbon::now()->startOfDay();
        $editable = $workHour->t_date->gt($now);

        return [
            'allDay' => false,
            'editable' => $editable,
            'forceEventDuration' => true,
            'id' => $workHour->id,
            'item_source' => $workHour,
            'title' => '',
            'type' => $editable ? 'workTime' : 'oldDays',
            'start' => $workHour->t_date->toIso8601String(),
            'end' => $workHour->t_date->addMinutes($workHour->length)->toIso8601String(),
        ];
    }

    /**
     * @return array
     */
    public function getAppointments()
    {

        $id = (int) $this->providerId;

        $cancelStatusesIds = Status::getOtherCancelStatusesId();
        $cancelStatusesIdsStr = '(' . implode(',', $cancelStatusesIds) . ')';
        $excludeOfficeRooms = '(' . OfficeRoom::query()->where('name', '=', '')->pluck('id')->implode(',') . ')';
        $data = Appointment::query()
            ->select([
                '*',
            DB::raw('DATE(FROM_UNIXTIME(time)) AS appt_date'),
            ])->with([
                'patient',
                'office',
                'officeRoom'
            ])->whereHas('patient')
            ->where('providers_id', $id)
            ->whereNotIn('appointment_statuses_id', $cancelStatusesIds)
            ->havingRaw("appt_date >= DATE('{$this->startDate->toDateString()}')")
            ->havingRaw("appt_date <= DATE('{$this->endDate->toDateString()}')")
            ->get();

        return $data;
    }

    protected function getAppointmentsDataset()
    {
        $appointments = $this->getAppointments();
        $dataset = [];
        foreach ($appointments as $appointment) {
            $apptDate = Carbon::createFromTimestamp($appointment->time);
            $dataset[] = [
                'id' => $appointment->id,
                'title' => '',
                'patient_id' => $appointment->patients_id,
                'allDay' => false,
                'start' => $apptDate->toIso8601String(),
                'end' => $apptDate->addMinutes($appointment->visit_length)->toIso8601String(),
                'forceEventDuration' => true,
                'item_source' => $appointment,
                'editable' => false,
                'type' => 'appointment',
            ];
        }

        return $dataset;
    }
}

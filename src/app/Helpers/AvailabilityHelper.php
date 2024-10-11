<?php
/**
 * Created by PhpStorm.
 * User: eremenko_aa
 * Date: 22.04.2018
 * Time: 11:32
 */

namespace App\Helpers;


use App\Availability;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AvailabilityHelper
{
    /**
     * @param Availability $event
     * @param Request $request
     * @param string $mode
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getUpcomingEvents(Availability $event, Request $request = null, $mode = 'all') {

        if(is_null($request)) {
            $length = $event->length;
            $at = $event->start_time;
            $on = $event->day_of_week;
            $eventStartDate = $event->start_date;
        } else {
            $length = $request->get('length');
            $at = $request->get('at');
            $on = $request->get('on');
            $eventStartDate = Carbon::parse($request->get('start_date'));
        }

        $endEditedEventTime = Carbon::createFromFormat('H:i:s', $at)
            ->addMinutes($length)
            ->format('H:i:s');

//        dd($length, $at, $on, $eventStartDate->toDateString());

        $events = Availability::query()
            ->select([
                '*',
                DB::raw("(`start_time` + INTERVAL `length` MINUTE) AS end_time"),
            ])
            ->where(function($query) use ($eventStartDate) {
                $query->whereDate('start_date', '>', $eventStartDate->toDateString());
                $query->orWhereNull('end_date');
                $query->orWhere(function($query) use ($eventStartDate) {
                    $query->whereNotNull('end_date');
                    $tmp = clone $eventStartDate;
                    $tmp->endOfDay();
                    $query->where('end_date', '>', $tmp->toDateTimeString());
                });
            })
            ->where('day_of_week', '=', $on)
            ->havingRaw("
                (start_time <= '$at' AND end_time > '$at') OR (start_time >= '$at' AND start_time < '$endEditedEventTime')
            ")
            ->whereNull('deleted_at')
            ->where('provider_id', $event->provider_id)
            ->when($mode === 'recurring', function($query) {
                $query->where('repeat', '>', 0);
            })->when(!is_null($event->id), function($query) use ($event) {
                $query->where('id', '!=', $event->id);
            })
            ->when($mode === 'single', function($query) {
                $query->where('repeat', '=', 0);
            })
            ->orderBy('start_date')
            ->orderBy('start_time')
            ->get();

//        dd($events->toArray());

        return !is_null($events) ? $events : collect([]);
    }

    /**
     * @param $dayOfWeek
     *
     * @return int
     */
    public static function getDayOfWeek($dayOfWeek)
    {
        $dayOfWeek--;
        if ($dayOfWeek < 0) {
            $dayOfWeek = 6;
        }

        return $dayOfWeek;
    }

    /**
     * @param string $week
     *
     * @return Carbon | null
     */
    public static function getWeekStartDate(string $week) {
        try {
            $dates = explode(' - ',  $week);
            return Carbon::parse($dates[0])->startOfWeek();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     *
     * @return int
     */
    public static function getWeeksCount(Carbon $startDate, Carbon $endDate)
    {
        return $startDate->diffInWeeks($endDate->copy()->addDays(1));
    }

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     *
     * @return array
     */
    public static function getWeeks(Carbon $startDate, Carbon $endDate, $fullWeeksOnly = true)
    {
        $weeks = [];
        $start = $startDate->copy();
        $end = $endDate->copy()->addWeek(1);
        
        while ($start->weekOfYear !== $end->weekOfYear) {
            $weeks[] = [
                'from' => $start->copy()->startOfWeek(),
                'to' => $start->copy()->endOfWeek()
            ];

            $start->addWeek(1);
        }

        if (count($weeks)) {
            $weeks[0]['from'] = $startDate->copy();
            $weeks[count($weeks) - 1]['to'] = $endDate->copy();
        }

        if (!$fullWeeksOnly) {
            return $weeks;
        }

        $fullWeeks = [];
        foreach ($weeks as $week) {
            $daysCount = $week['from']->diffInDays($week['to']);
            if ($daysCount == 6) {
                $fullWeeks[] = $week;
            }
        }

        return $fullWeeks;
    }
}
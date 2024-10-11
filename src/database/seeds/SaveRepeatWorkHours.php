<?php

use Illuminate\Database\Seeder;
use App\Jobs\Availability\GetProviderWorkHours;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Jobs\Availability\SaveToNewTable;

class SaveRepeatWorkHours extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $notEmptyProviders = DB::table('provider_work_hours')
            ->select('provider_id')->groupBy('provider_id')
            ->get()->pluck('provider_id');
        $start = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks(2);
        $endDate = Carbon::now()->endOfWeek()->addWeeks(2);
        $weeks = $endDate->diffInWeeks($start);
        foreach($notEmptyProviders as $provider_id) {
            $weekArr = [];
            for($k=0; $k<=$weeks; $k++) {
                $startWeek = (clone $start)->addWeeks($k);
                $startDate = $startWeek->toIso8601String();
                $endWeek = $startWeek->endOfWeek()->toIso8601String();
                $week = \Bus::dispatchNow(new GetProviderWorkHours($startDate, $endWeek, false, false, $provider_id));
                    foreach($week as $event) {
                        $availabilityEvent = $event['item_source'];
                        $startDateStartOfDay = $availabilityEvent->t_date;
                        $startDateStartOfDay = $startDateStartOfDay->startOfDay();
                        $now = Carbon::now();
                        $payload = [
                            'provider_id' => $availabilityEvent->provider_id,
                            'office_id' => $availabilityEvent->office_id,
                            'office_room_id' => $availabilityEvent->office_room_id,
                            'day_of_week' => $availabilityEvent->day_of_week,
                            'start_time' => $availabilityEvent->start_time,
                            'length' => $availabilityEvent->length,
                            'start_date' => $startDateStartOfDay,                           
                        ];
                        $keyEvent = implode('_', $payload);
                        $payload['created_at'] = $now;
                        $payload['updated_at'] = $now;
                        if($startDateStartOfDay->greaterThanOrEqualTo($start)) {
                            $weekArr[$keyEvent] = $payload;
                        }
                    }
            }
            if(!empty($weekArr)) {
                \Bus::dispatchNow(new SaveToNewTable($weekArr));
            }
        }
    }
}

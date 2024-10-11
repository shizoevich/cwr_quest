<?php

namespace App\Jobs\Availability;

use App\Helpers\AvailabilityHelper;
use App\Availability;
use App\AvailabilityType;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Auth;

class AddWorkHours implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Request
     */
    private $request;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response = [
            'success' => true,
        ];
        $request = $this->request;
        $startDate = Carbon::parse($request->get('start_date'));
        $startDateStartOfDay = clone $startDate;
        $startDateStartOfDay = $startDateStartOfDay->startOfDay();
        $workHour = new Availability([
            'office_id' => $request->get('office_id'),
            'office_room_id' => $request->get('office_room_id'),
            'start_time' => $startDate->format('H:i:s'),
            'day_of_week' => $startDate->format('N') - 1,
            'length' => $request->get('length'),
            'provider_id' => (Auth::user())->provider_id,
            'start_date' => $startDateStartOfDay,
            'in_person' => $request->get('in_person'),
            'virtual' => $request->get('virtual'),
            'availability_type_id' => $request->get('availability_type_id'),
            'availability_subtype_id' => $request->get('availability_subtype_id'),
            'comment' => $request->get('comment')
        ]);

        $workHour->save();

        return $response;
    }
}

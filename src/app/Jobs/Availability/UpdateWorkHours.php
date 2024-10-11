<?php

namespace App\Jobs\Availability;

use App\Availability;
use App\AvailabilitySubtype;
use App\AvailabilityType;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Auth;

class UpdateWorkHours implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Request
     */
    private $request;


    /**
     * UpdateWorkHours constructor.
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
        $request = $this->request;
        $startDate = Carbon::parse($request->get('start_date'))->startOfDay();
        $event = Availability::where('id', $request->get('id'))->firstOrFail();
        $response = [
            'success' => true,
        ];
        if ($event->provider_id != Auth::user()->provider_id) {
            $response['success'] = false;
            return $response;
        }

        $availability_subtype_id = $request->get('availability_subtype_id');
        $comment = $request->get('comment');

        if ($request->get('availability_type_id') === AvailabilityType::getForNewPatientsId()) {
            $availability_subtype_id = null;
            $comment = null;
        }

        if ($availability_subtype_id === AvailabilitySubtype::getIdByTypeRescheduling()) {
            $comment = null;
        }

        $data = [
            'office_id' => $request->get('office_id'),
            'office_room_id' => $request->get('office_room_id'),
            'start_time' => $request->get('at'),
            'day_of_week' => $request->get('on'),
            'length' => $request->get('length'),
            'start_date' => $startDate->startOfDay(),
            'provider_id' => Auth::user()->provider_id,
            'availability_type_id' => $request->has('availability_type_id') ? $request->availability_type_id : AvailabilityType::getForNewPatientsId(),
            'availability_subtype_id' => $availability_subtype_id,
            'comment' => $comment
        ];
        if ($request->has('in_person')) {
            $data['in_person'] = $request->get('in_person');
        }
        if ($request->has('virtual')) {
            $data['virtual'] = $request->get('virtual');
        }

        $event->update($data);
    }
}

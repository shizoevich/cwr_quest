<?php

namespace App\Http\Controllers;

use App\Appointment;
use App\Http\Requests\Availability\GetTotalWorkHoursRequest;
use App\Jobs\Availability\AddWorkHours;
use App\Jobs\Availability\DeleteWorkHours;
use App\Jobs\Availability\UpdateWorkHours;
use App\ProviderWorkHourWeek;
use App\Repositories\Provider\Availability\ProviderAvailabilityRepositoryInterface;
use App\Status;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Jobs\Availability\GetProviderWorkHoursNew;
use App\Availability;
use App\Http\Requests\Availability\AddWorkHoursRequest;
use App\Http\Requests\Availability\CopyPreviousWeekRequest;
use App\Http\Requests\Availability\GetWorkHoursRequest;
use App\Http\Requests\Availability\UpdateWorkHoursRequest;
use App\Jobs\Availability\SaveToNewTable;
use App\WeekConfirmation;

class ProviderAvailabilityCalendarController extends Controller
{
    use PatientTrait, OfficeTrait, StatusTrait, RemoveArrayItems;

    protected $providerAvailabilityRepository;

    public function __construct(ProviderAvailabilityRepositoryInterface $providerAvailabilityRepository) {
        $this->middleware('user-provider');
        $this->providerAvailabilityRepository = $providerAvailabilityRepository;
    }

    public static function getAppointments(Request $request, $id = null)
    {

        if(is_null($id)) {
            $id = (Auth::user())->provider_id;
        }

        $cancelStatusesIds = Status::getStatusesIdLikeCancel();

        $data = Appointment::select()
            ->with(['patient','office','officeRoom'])
            ->inCurrentWeek()
            ->where('providers_id', $id)
            ->whereNotIn('appointment_statuses_id',$cancelStatusesIds)
            ->get();

        return $data->toArray();
    }

    public static function getWorkHours(GetWorkHoursRequest $request)
    {
        $startDate = is_null($request->input('start')) ? null : Carbon::parse($request->input('start'));
        $endDate = is_null($request->input('end')) ? null : Carbon::parse($request->input('end'));
        if(is_null($startDate) || is_null($endDate)) {
            return response([]);
        }

        $options = [
            'with_active_appointments' => (bool) $request->input('with_active_appointments'),
            'with_canceled_appointments' => (bool) $request->input('with_canceled_appointments'),
            'with_rescheduled_appointments' => (bool) $request->input('with_rescheduled_appointments'),
            'with_rescheduled_appointments_date' => (bool) $request->input('with_rescheduled_appointments'),
        ];

        $workHours = \Bus::dispatchNow(new GetProviderWorkHoursNew($startDate, $endDate, $options));

        return response($workHours);
    }

    public static function getScopeByWeeks(Request $request)
    {
        $startDate = is_null($request->input('start')) ? null : Carbon::parse($request->input('start'));
        $endDate = is_null($request->input('end')) ? null : Carbon::parse($request->input('end'));
        if(is_null($startDate) || is_null($endDate)) {
            return response([]);
        }

        $options = [
            'with_active_appointments' => false,
            'with_canceled_appointments' => false,
            'with_rescheduled_appointments' => false,
            'all_providers' => true
        ];

        $data = \Bus::dispatchNow(new GetProviderWorkHoursNew($startDate, $endDate, $options));
        $result = [];
        foreach ($data as $providerWeek) {
            if(!array_key_exists($providerWeek['item_source']['provider_id'], $result)) {
                $result[$providerWeek['item_source']['provider_id']] = [
                    'score' => 0,
                    'availability_ids' => [],
                ];
            }
            if(!in_array($providerWeek['item_source']['id'], $result[$providerWeek['item_source']['provider_id']]['availability_ids'] )) {
                $result[$providerWeek['item_source']['provider_id']]['score'] += $providerWeek['item_source']['length'] / 60;
                $result[$providerWeek['item_source']['provider_id']]['availability_ids'][] = $providerWeek['item_source']['id'];
            }
        }

        return response($result);
    }


    public static function addWorkHours(AddWorkHoursRequest $request)
    {
        $response = \Bus::dispatchNow(new AddWorkHours($request));

        return $response;
    }

    public static function updateWorkHours(UpdateWorkHoursRequest $request)
    {
        $response = \Bus::dispatchNow(new UpdateWorkHours($request));

        return response($response);
    }

    public static function deleteWorkHours(Request $request)
    {
        $result = \Bus::dispatchNow(new DeleteWorkHours($request));

        return response([
            'result' => $result,
        ]);
    }

    public static function getWeeksStatus(Request $request)
    {
        $result = ProviderWorkHourWeek::select('*')
            ->whereProvider((Auth::user())->provider_id)
            ->where('year' , Carbon::now()->year)
            ->get();

        return response($result);
    }

    public function getEventMaxTime(Request $request)
    {
        $providerId = auth()->user()->provider_id;
        $time = $request->get('time');
        $date = $request->get('date');
        $eventId = (int)$request->get('event_id');

        $event = Availability::query()
            ->where('provider_id', $providerId)
            ->where('start_date', $date)
            ->where(function ($query) use ($time) {
                $query
                    ->where('start_time', '>=', $time)
                    ->orWhere(function ($query) use ($time) {
                        $query->where('start_time', '<=', $time)
                            ->where(\DB::raw('DATE_ADD(start_time, INTERVAL length MINUTE)'), '>', $time);
                    });
            })
            ->when(!empty($eventId), function ($query) use ($eventId) {
                $query->where('id', '!=', $eventId);
            })
            ->orderBy('start_time')
            ->first();

        if (!empty($event)) {
            if ($time >= $event->start_time && $time < Carbon::parse($event->start_time)->addMinutes($event->length)) {
                return $time;
            }

            return $event->start_time;
        }

        return '22:00:00';
    }

    public static function makeWeekCompleted(Request $request)
    {
        $week = new ProviderWorkHourWeek([
            'provider_id' => (Auth::user())->provider_id,
            'week' => $request->get('week'),
            'year' => Carbon::now()->year,
            'status' => 1
        ]);
        $week->save();
        $result = ProviderWorkHourWeek::select('*')->whereProvider((Auth::user())->provider_id)->get();

        return response($result);
    }

    public static function checkWeeksCompleted()
    {

        $result = ProviderWorkHourWeek::select()
            ->whereProvider((Auth::user())->provider_id)
            ->where('year' , Carbon::now()->year)
            ->whereIn('week' , [Carbon::now()->weekOfYear, Carbon::now()->weekOfYear+1])
            ->count();

        return response(['status' => is_null((Auth::user())->provider_id) ||$result == 2]);
    }

    public function checkCopy(CopyPreviousWeekRequest $request)
    {
        $user = auth()->user();
        $start = Carbon::parse($request->get('start'))->startOfWeek();
        $end = $start->copy()->endOfWeek();
        $prevWeekStart = $start->copy()->subDay(1)->startOfWeek();
        $prevWeekEnd = $prevWeekStart->copy()->endOfWeek();

        $lastAvailability = Availability::query()
            ->where('provider_id', '=', $user->provider_id)
            ->whereDate('start_date', '>=', $prevWeekStart->toDateString())
            ->whereDate('start_date', '<=', $prevWeekEnd->toDateString())
            ->exists();
        if (!$lastAvailability) {
            return response()
                ->json([
                    'message' => 'No data found for the previous week'
                ], 422);
        }

        $fromFormated = $this->formatPeriod($prevWeekStart, $prevWeekEnd);
        $toFormated = $this->formatPeriod($start, $end);
        return response()
            ->json([
                'message' => "Would you like to duplicate your availability from {$fromFormated}? This will overwrite any existing availability for {$toFormated}",
            ], 200);
    }

    public function formatPeriod($startDate, $endDate)
    {
        $response = $startDate->format('M d');
        if ($startDate->isSameMonth($endDate)) {
            $response.= ' - ' . $endDate->format('d, Y');
        } else {
            if ($startDate->isSameYear($endDate)) {
                $response.= ' - ' . $endDate->format('M d, Y');
            } else {
                $response.= ' ' . $startDate->format('Y') . ' - ' . $endDate->format('M d Y');
            }
        }
        return $response;
    }
    
    /**
     * @todo refactor this code
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function copyLast(CopyPreviousWeekRequest $request)
    {
        $user = auth()->user();
        $start = Carbon::parse($request->get('start'))->startOfWeek();
        $end = $start->copy()->endOfWeek();
        Availability::query()
            ->where('provider_id', $user->provider_id)
            ->whereDate('start_date', '>=', $start->toDateString())
            ->whereDate('start_date', '<=', $end->toDateString())
            ->get()
            ->each(function ($availability) {
                $availability->delete();
            });
        
        $prevWeekStart = $start->copy()->subWeek()->startOfWeek();
        $prevWeekEnd = $prevWeekStart->copy()->endOfWeek();

        Availability::query()
            ->where('provider_id', '=', $user->provider_id)
            ->whereDate('start_date', '>=', $prevWeekStart)
            ->whereDate('start_date', '<=', $prevWeekEnd)
            ->get()
            ->each(function ($availability) use ($request){
                $newAvailability = $availability->replicate();
                $newAvailability->start_date = Carbon::parse($request->get('start'));
                $newAvailability->save();
            });

        return response('', 200);
    }

    public function confirmWeek(Request $request)
    {
        $createArr = array_merge(
            $request->all(),
            [
                'provider_id' => auth()->user()->provider_id,
                'status' => 1
            ]
        );
        $weekConfirmation = WeekConfirmation::create($createArr);
        return $weekConfirmation;
    }

    public function getTotalWorkHours(GetTotalWorkHoursRequest $request)
    {
        $provider = (Auth::user())->provider;
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $totalAvailability = $this->providerAvailabilityRepository->getTotalAvailabilityForPeriod($provider, $startDate, $endDate, true);
        $totalAvailabilityHours = $this->providerAvailabilityRepository->getTotalAvailabilityHours($totalAvailability);

        return response()->json([
            'active_hours' => $totalAvailabilityHours['activeAppointmentsLength'],
            'completed_hours' => $totalAvailabilityHours['completedAppointmentsLength'],
            'visit_created_hours' => $totalAvailabilityHours['visitCreatedAppointmentsLength'],
            'cancelled_hours' => $totalAvailabilityHours['canceledAppointmentsLength'],
            'for_appts_availability_hours' => $totalAvailabilityHours['forApptsRemainingAvailabilityLength'],
            'rescheduling_availability_hours' => $totalAvailabilityHours['reschedulingRemainingAvailabilityLength'],
            'minimum_work_hours' => $this->providerAvailabilityRepository->getMinimumWorkHoursForPeriod($provider, $startDate, $endDate),
        ]);
    }

    public function checkWeekConfirmation(Request $request)
    {
        if(auth()->user()->isInsuranceAudit()) {
            return response()->json([
                'confirmed' => true,
            ]);
        }
        $weekConfirmed = WeekConfirmation::whereWeek($request->get('week'))
            ->where('year', $request->get('year'))
            ->whereProviderId(auth()->user()->provider_id)
            ->exists();
        
        return response()->json([
            'confirmed' => $weekConfirmed,
        ]);
    }
}

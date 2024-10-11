<?php

namespace App\Http\Controllers;

use App\Appointment;
use App\Http\Requests\GetFreeRooms;
use App\OfficeRoom;
use App\Status;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OfficeRoomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rooms = OfficeRoom::orderBy('office_id')
            ->orderBy('name')
            ->get();

        return $rooms;
    }

    /**
     * Return rooms which free in time that sent from Vue.js
     *
     * @param GetFreeRooms $request
     * @return array
     */
    public function getFreeRooms(GetFreeRooms $request)
    {

        $startTime = $request->query('startTime');
        $endTime = $request->query('endTime');
        $cancelStatusesIds = Status::getStatusesIdLikeCancel();

        $freeOffices = OfficeRoom::whereDoesntHave(
            'appointments', function ($query) use ($startTime, $endTime, $cancelStatusesIds) {
            $query
                ->whereBetween('time', [$startTime, $endTime])
                // add one more rule, that checks is start time of appointment + appointment length in seconds is between event time
                ->orWhereRaw('time + visit_length * 60 between ? and ?', [$startTime, $endTime])
                ->whereNotIn('appointment_statuses_id', $cancelStatusesIds);
        })->groupBy('name')->orderBy('name')->get()
            ->groupBy('office_id')
            ->map(function ($value) {
                return $value->pluck('name');
            })
            ->toArray();

        return $freeOffices;
    }
}

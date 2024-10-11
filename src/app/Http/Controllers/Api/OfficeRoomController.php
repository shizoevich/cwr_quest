<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\OfficeRoom\Index as IndexRequest;
use App\Office;
use App\Repositories\OfficeRoom\OfficeRoomRepositoryInterface;
use App\Http\Controllers\Controller;

class OfficeRoomController extends Controller
{
    /**
     * @var OfficeRoomRepositoryInterface
     */
    protected $officeRoomRepository;

    /**
     * OfficeRoomController constructor.
     * @param OfficeRoomRepositoryInterface $officeRoomRepository
     */
    public function __construct(OfficeRoomRepositoryInterface $officeRoomRepository)
    {
        $this->officeRoomRepository = $officeRoomRepository;
    }

    /**
     * @param IndexRequest $request
     * @param Office $office
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(IndexRequest $request, Office $office)
    {
        return response()->json([
            'office_rooms' => $this->officeRoomRepository->getAllFree([
                'office_id' => $office->id,
                'from' => $request->from,
                'to' => $request->to,
            ])
        ]);
    }
}

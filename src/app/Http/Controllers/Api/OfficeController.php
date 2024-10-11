<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Office\Index;
use App\Repositories\Office\OfficeRepositoryInterface;
use App\Http\Controllers\Controller;

class OfficeController extends Controller
{
    /**
     * @var OfficeRepositoryInterface
     */
    protected $officeRepository;

    /**
     * OfficeController constructor.
     * @param OfficeRepositoryInterface $officeRepository
     */
    public function __construct(OfficeRepositoryInterface $officeRepository)
    {
        $this->officeRepository = $officeRepository;
    }

    /**
     * @param Index $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Index $request)
    {
        return response()->json([
            'offices' => $this->officeRepository->all()
        ]);
    }
}

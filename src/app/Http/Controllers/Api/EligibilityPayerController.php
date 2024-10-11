<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\EligibilityPayer\Index as IndexRequest;
use App\Repositories\EligibilityPayer\EligibilityPayerRepositoryInterface;
use App\Http\Controllers\Controller;

class EligibilityPayerController extends Controller
{
    /**
     * @var EligibilityPayerRepositoryInterface
     */
    protected $eligibilityPlayerRepository;

    /**
     * EligibilityPayerController constructor.
     * @param EligibilityPayerRepositoryInterface $eligibilityPlayerRepository
     */
    public function __construct(EligibilityPayerRepositoryInterface $eligibilityPlayerRepository)
    {
        $this->eligibilityPlayerRepository = $eligibilityPlayerRepository;
    }

    /**
     * @param IndexRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(IndexRequest $request)
    {
        return response()->json($this->eligibilityPlayerRepository->all($request->limit, $request->search_query));
    }
}

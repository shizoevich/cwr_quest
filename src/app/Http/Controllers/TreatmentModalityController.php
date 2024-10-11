<?php

namespace App\Http\Controllers;

use App\Http\Requests\TreatmentModality\IndexRequest;
use App\Repositories\TreatmentModality\TreatmentModalityRepositoryInterface;

class TreatmentModalityController extends Controller
{
    /**
     * @var TreatmentModalityRepositoryInterface
     */
    protected $treatmentModalityRepository;

    /**
     * TreatmentModalityController constructor.
     * @param TreatmentModalityRepositoryInterface $treatmentModalityRepository
     */

    public function __construct(TreatmentModalityRepositoryInterface $treatmentModalityRepository)
    {
        $this->treatmentModalityRepository = $treatmentModalityRepository;
    }

    public function index(IndexRequest $request)
    {
        return response()->json($this->treatmentModalityRepository->getAll($request->validated()));
    }
}

<?php

namespace App\Http\Controllers\Api\Patient;

use App\Http\Controllers\Controller;
use App\Patient;
use App\Repositories\Patient\CheckChargeCancellationRepositoryInterface;
use Illuminate\Http\Request;

class CheckChargeForCancellationController extends Controller
{
    /**
     * @var CheckChargeCancellationRepositoryInterface
     */
    protected $checkChargeCancellationRepository;

    /**
     * CheckChargeForCancellationController constructor.
     * @param CheckChargeCancellationRepositoryInterface $checkChargeCancellationRepository
     */
    public function __construct(CheckChargeCancellationRepositoryInterface $checkChargeCancellationRepository)
    {
        $this->checkChargeCancellationRepository = $checkChargeCancellationRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        return response()
            ->json($this
                ->checkChargeCancellationRepository
                ->getPatientsWithDocumentName($request->query('statusId')));
    }

    public function update(Request $request, Patient $patient)
    {
        $this->validate($request, [
            'cancellationFee' => '|numeric|min:0',
        ]);

        return response()
            ->json($this
                ->checkChargeCancellationRepository
                ->updateCancellationFee($request->input('cancellationFee'), $patient));
    }
}

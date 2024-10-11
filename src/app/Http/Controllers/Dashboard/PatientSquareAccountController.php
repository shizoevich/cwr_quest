<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\PatientSquareAccount\GetUnattached as GetUnattachedRequest;
use App\Http\Requests\PatientSquareAccount\Update as UpdateRequest;
use App\Http\Requests\PatientSquareAccount\Detach as DetachRequest;
use App\Jobs\Patients\CalculatePatientBalance;
use App\Models\Patient\PatientPreprocessedTransaction;
use App\Models\Patient\PatientTransaction;
use App\Models\Patient\PatientTransactionAdjustment;
use App\Models\Square\SquareTransaction;
use App\Patient;
use App\PatientSquareAccount;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;

class PatientSquareAccountController extends Controller
{
    /**
     * @param GetUnattachedRequest $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function unattached(GetUnattachedRequest $request)
    {
        $accounts = PatientSquareAccount::query()
            ->select([
                'patient_square_accounts.*',
                \DB::raw("CONCAT(
                    IF(first_name IS NOT NULL, first_name, ''),
                    ' ',
                    IF(last_name IS NOT NULL, last_name, '')
                ) AS full_customer_name"),
            ])
            ->unattached()
            ->when($request->has('q') && !empty($request->get('q')), function (Builder $query) use (&$request) {
                $q = $request->get('q');
                $query->having('full_customer_name', 'like', "%$q%");
            })
            ->where(function (Builder $query) {
                $query->whereNotNull('first_name');
                $query->orWhereNotNull('last_name');
            })
            ->with([
                'transactions' => function ($query) {
                    $query->select([
                        'square_transactions.*',
                        'square_transaction_types.name AS method',
                        'square_card_brands.name AS card_brand',
                    ]);
                    $query->join('square_transaction_types', 'square_transaction_types.id', '=', 'square_transactions.transaction_type_id');
                    $query->leftJoin('square_card_brands', 'square_card_brands.id', '=', 'square_transactions.card_brand_id');

                },
            ])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('dashboard.square.customers.unattached', [
            'accounts' => $accounts,
        ]);
    }

    /**
     * @param UpdateRequest $request
     * @param PatientSquareAccount $customer
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, PatientSquareAccount $customer)
    {
        $customer->patient_id = $request->get('patient_id');
        $customer->save();
        dispatch(new CalculatePatientBalance());
        $patient = Patient::find($customer->patient_id);

        return redirect()->back()->with([
            'message' => "Customer <b>{$customer->first_name} {$customer->last_name}</b> has been attached to patient <b>{$patient->first_name} {$patient->last_name}</b>.",
        ]);
    }

    /**
     * @param Patient $patient
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPatientCustomers(Patient $patient)
    {
        $customers = $patient->squareAccounts;

        return response()->json([
            'customers' => $customers,
        ]);
    }

    /**
     * @param DetachRequest $request
     * @param PatientSquareAccount $customer
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function detach(DetachRequest $request, PatientSquareAccount $customer)
    {
        if ($customer->patient_id != $request->get('patient_id')) {
            abort(403);
        }
        $customer->update(['patient_id' => null]);
        $adjustmentAmount = $customer->transactions()->preprocessed()->sum('amount_money');
        $customer->transactions()
            ->preprocessed()
            ->select('id')
            ->chunk(100, function (Collection $transactions) use (&$request) {
                PatientTransaction::query()
                    ->where('patient_id', $request->get('patient_id'))
                    ->whereIn('transactionable_id', $transactions->pluck('id'))
                    ->where('transactionable_type', SquareTransaction::class)
                    ->whereNull('detached_at')
                    ->each(function ($transaction) {
                        $transaction->update([
                            'detached_at' => Carbon::now(),
                        ]);
                    });
                PatientPreprocessedTransaction::query()
                    ->where('patient_id', $request->get('patient_id'))
                    ->whereIn('transactionable_id', $transactions->pluck('id'))
                    ->where('transactionable_type', SquareTransaction::class)
                    ->whereNull('detached_at')
                    ->each(function ($preprocessedTransaction) {
                        $preprocessedTransaction->update([
                            'detached_at' => Carbon::now(),
                        ]);
                    });
            });

        $customer->transactions()
            ->each(function ($transaction) {
                $transaction->update([
                    'processed_at'    => null,
                    'preprocessed_at' => null,
                ]);
            });

        if ($adjustmentAmount > 0) {
            PatientTransactionAdjustment::addAdjustment(
                $request->get('patient_id'),
                -1 * $adjustmentAmount,
                "Square Customer <b>{$customer->first_name} {$customer->last_name}</b> has been Detached",
                null
            );
            \Bus::dispatchNow(new CalculatePatientBalance([$request->get('patient_id')]));
        }

        return response([], 204);
    }
}

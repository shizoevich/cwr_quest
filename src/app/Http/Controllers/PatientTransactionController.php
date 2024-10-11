<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\AccessUtils;
use App\Http\Requests\Posing\MakePosting;
use App\Models\Officeally\OfficeallyTransaction;
use App\Models\Patient\PatientPreprocessedTransaction;
use App\Models\Patient\PatientTransactionAdjustment;
use App\Models\Square\SquareTransaction;
use App\Models\LateCancellationTransaction;
use App\Patient;
use App\Repositories\Patient\PreprocessedTransactionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PatientTransactionController extends Controller
{
    use AccessUtils;

    protected $preprocessedTransactionRepository;

    public function __construct(PreprocessedTransactionRepositoryInterface $preprocessedTransactionRepository) 
    {
        $this->preprocessedTransactionRepository = $preprocessedTransactionRepository;
    }

    public function getPreprocessed(Request $request, $patientId)
    {
        if (!$this->isUserHasAccessRightsForPatient($patientId, null, true)) {
            if ($request->expectsJson()) {
                return response([], 403);
            }

            abort(403);
        }

        $transactionsBuilder = PatientPreprocessedTransaction::where('patient_preprocessed_transactions.patient_id', $patientId)
            ->select([
                'patient_preprocessed_transactions.id',
                'patient_preprocessed_transactions.created_at',
                'patient_preprocessed_transactions.balance_after_transaction',
                'patient_preprocessed_transactions.transactionable_id',
                'patient_preprocessed_transactions.transactionable_type',
            ]);

        $squareTransactions = clone $transactionsBuilder;
        $officeallyTransactions = clone $transactionsBuilder;
        $adjustments = clone $transactionsBuilder;
        $lateCancellationTransactions = clone $transactionsBuilder;

        $squareTransactions = $squareTransactions->where('transactionable_type', SquareTransaction::class)
            ->join('square_transactions', 'square_transactions.id', '=', 'patient_preprocessed_transactions.transactionable_id')
            ->join('square_transaction_types', 'square_transaction_types.id', '=', 'square_transactions.transaction_type_id')
            ->leftJoin('square_card_brands', 'square_card_brands.id', '=', 'square_transactions.card_brand_id')
            ->leftJoin('square_orders', 'square_orders.id', '=', 'square_transactions.order_id')
            ->leftJoin('square_catalog_items', 'square_catalog_items.id', '=', 'square_orders.catalog_item_id')
            ->leftJoin('users', 'users.id', '=', 'square_transactions.user_id')
            ->leftJoin('users_meta', 'users.id', '=', 'users_meta.user_id')
            ->leftJoin('providers', 'providers.id', '=', 'users.provider_id')
            ->addSelect([
                'square_transactions.amount_money',
                'square_transaction_types.name AS transaction_type',
                'square_transactions.transaction_date',
                'square_card_brands.name AS card_brand',
                'square_transactions.card_last_four',
                DB::raw("COALESCE(providers.provider_name, CONCAT(users_meta.firstname, ' ', users_meta.lastname)) AS user_name"),
                DB::raw("NULL AS comment"),
                DB::raw("square_catalog_items.name AS catalog_item"),
                DB::raw("NULL AS appt_date"),
            ]);

        $officeallyTransactions = $officeallyTransactions->where('transactionable_type', OfficeallyTransaction::class)
            ->join('officeally_transactions', 'officeally_transactions.id', '=', 'patient_preprocessed_transactions.transactionable_id')
            ->join('officeally_transaction_types', 'officeally_transaction_types.id', '=', 'officeally_transactions.transaction_type_id')
            ->leftJoin('users', 'users.id', '=', 'officeally_transactions.user_id')
            ->leftJoin('users_meta', 'users.id', '=', 'users_meta.user_id')
            ->leftJoin('providers', 'providers.id', '=', 'users.provider_id')
            ->addSelect([
                DB::raw("officeally_transactions.payment_amount * -1 AS amount_money"),
                'officeally_transaction_types.name AS transaction_type',
                'officeally_transactions.transaction_date',
                DB::raw("NULL AS card_brand"),
                DB::raw("NULL AS card_last_four"),
                DB::raw("COALESCE(providers.provider_name, CONCAT(users_meta.firstname, ' ', users_meta.lastname)) AS user_name"),
                DB::raw("NULL AS comment"),
                DB::raw("NULL AS catalog_item"),
                DB::raw("NULL AS appt_date"),
            ]);

        $adjustments = $adjustments->where('transactionable_type', PatientTransactionAdjustment::class)
            ->join('patient_transaction_adjustments', 'patient_transaction_adjustments.id', '=', 'patient_preprocessed_transactions.transactionable_id')
            ->leftJoin('users', 'users.id', '=', 'patient_transaction_adjustments.user_id')
            ->leftJoin('users_meta', 'users.id', '=', 'users_meta.user_id')
            ->leftJoin('providers', 'providers.id', '=', 'users.provider_id')
            ->addSelect([
                'patient_transaction_adjustments.amount AS amount_money',
                DB::raw("'Adjustment' AS transaction_type"),
                'patient_transaction_adjustments.transaction_date',
                DB::raw("NULL AS card_brand"),
                DB::raw("NULL AS card_last_four"),
                DB::raw("COALESCE(providers.provider_name, CONCAT(users_meta.firstname, ' ', users_meta.lastname)) AS user_name"),
                'patient_transaction_adjustments.comment',
                DB::raw("NULL AS catalog_item"),
                DB::raw("NULL AS appt_date"),
            ]);

        $lateCancellationTransactions = $lateCancellationTransactions->where('transactionable_type', LateCancellationTransaction::class)
            ->join('late_cancellation_transactions', 'late_cancellation_transactions.id', '=', 'patient_preprocessed_transactions.transactionable_id')
            ->join('appointments', 'appointments.id', '=', 'late_cancellation_transactions.appointment_id')
            ->leftJoin('users', 'users.id', '=', 'late_cancellation_transactions.user_id')
            ->leftJoin('users_meta', 'users.id', '=', 'users_meta.user_id')
            ->leftJoin('providers', 'providers.id', '=', 'users.provider_id')
            ->addSelect([
                DB::raw("late_cancellation_transactions.payment_amount * -1 AS amount_money"),
                DB::raw("'Cancellation fee' AS transaction_type"),
                'late_cancellation_transactions.transaction_date',
                DB::raw("NULL AS card_brand"),
                DB::raw("NULL AS card_last_four"),
                DB::raw("COALESCE(providers.provider_name, CONCAT(users_meta.firstname, ' ', users_meta.lastname)) AS user_name"),
                DB::raw("NULL AS comment"),
                DB::raw("NULL AS catalog_item"),
                DB::raw("FROM_UNIXTIME(`appointments`.`time`) AS appt_date"),
            ]);

        $transactions = $officeallyTransactions
            ->union($squareTransactions)
            ->union($adjustments)
            ->union($lateCancellationTransactions)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($transactions);
    }


    public function getOfficeallyPaymentsForPosting(Request $request) {

        if(Auth::user()->isSecretary()) {
            abort(403);
        }

        $statuses = $request->input('statuses');
        $appliedStatus = ($statuses['applied']['selected'] == "true");
        $unappliedStatus = ($statuses['unapplied']['selected'] == "true");
        $dataset = [];

        $month = $request->filled('month') ? Carbon::createFromFormat('d F Y', $request->month) : Carbon::now();
        $dateFrom = $request->filled('date_from') ? Carbon::createFromFormat('m/d/Y', $request->date_from) : null;
        $dateTo = $request->filled('date_to') ? Carbon::createFromFormat('m/d/Y', $request->date_to) : null;

        $filterType = 2;
        if($request->filled('selected_filter_type')) {
            $filterType = $request->selected_filter_type;
        }

        if($appliedStatus || $unappliedStatus) {
            $payments = OfficeallyTransaction::select([
                'officeally_transactions.id AS transaction_id',
                'officeally_transactions.patient_id',
                'officeally_transactions.external_id AS external_transaction_id',
                'officeally_transaction_types.name AS transaction_type',
                'officeally_transactions.payment_amount',
                'officeally_transactions.applied_amount',
                'officeally_transactions.transaction_date',
                'officeally_transactions.processed_at',
                'officeally_transactions.start_posting_date',
                'officeally_transactions.is_warning',                
                'officeally_transactions.error_message',
                DB::raw("DATE(officeally_transactions.transaction_date) AS payment_date"),
                DB::raw("MONTH(officeally_transactions.transaction_date) AS payment_month"),
                DB::raw("YEAR(officeally_transactions.transaction_date) AS payment_year"),
                DB::raw("IF(officeally_transactions.applied_amount < 0, 1, 0) AS is_applied"),
            ])
                ->with([
                    'patient' => function($query) {
                        $query->select([
                            'id',
                            DB::raw("CONCAT(first_name, ' ', last_name) AS name"),
                        ]);
                        $query->with([
                            'balance' => function($query) {
                                $query->select([
                                    'patient_id',
                                    'balance_after_transaction AS balance',
                                ]);
                            },
                        ]);
                    },
                ])
                ->join('patients', 'patients.id', '=', 'officeally_transactions.patient_id')
                ->join('officeally_transaction_types', 'officeally_transaction_types.id', '=', 'officeally_transactions.transaction_type_id')
                ->where('patients.is_test', 0)
                ->orderBy('officeally_transactions.transaction_date', 'desc')
                ->orderBy('officeally_transactions.id', 'desc');

            if($appliedStatus && !$unappliedStatus) {
                $payments->where('applied_amount', '<', 0);
            } else if(!$appliedStatus && $unappliedStatus) {
                $payments->where('applied_amount', '=', 0);
            }

            switch($filterType) {
                case 1:
                    if(is_null($dateFrom)) {
                        $dateFrom = Carbon::now();
                    }
                    $payments = $payments->havingRaw("payment_date = date('" . $dateFrom->format('Y-m-d') . "')");
                    break;
                case 2:
                    if(is_null($dateFrom)) {
                        $dateFrom = Carbon::now()->subWeek();
                    }
                    if(is_null($dateTo)) {
                        $dateTo = Carbon::now();
                    }
                    $payments = $payments->havingRaw("payment_date >= date('{$dateFrom->toDateString()}') AND payment_date <= date('{$dateTo->toDateString()}')");
                    break;
                case 3:
                    $payments = $payments->havingRaw("payment_month = {$month->month} AND payment_year = {$month->year}");
                    break;
            }

            $payments = $payments->get();

            foreach($payments as $payment) {
                if(is_null($payment->patient->balance)) {
                    $payment->has_balance = false;
                } else {
                    $balance = $payment->patient->balance->balance / 100;
                    if($balance > 0) {
                        $paymentAmount = $payment->payment_amount / 100;
                        $appliedAmount = $payment->applied_amount / 100;
                        $payment->has_balance = !($appliedAmount == 0 && ($balance - $paymentAmount) < 0);
                    } else {
                        $payment->has_balance = false;
                    }

                }

                $payment->allow_posting = $payment->has_balance && !$payment->is_applied && !$payment->start_posting_date && (!$payment->error_message || $payment->is_warning);

                $dataset[$payment->payment_date]['dataset'][] = $payment;
                $dataset[$payment->payment_date]['date'] = Carbon::createFromFormat('Y-m-d', $payment->payment_date)->format('m/d/Y');
            }
        }
        $month = null;
        if($request->has('month') && !is_null($request->month)) {
            $month = Carbon::createFromFormat('d F Y', $request->month)->format('d F Y');
        } else {
            $month = Carbon::now()->format('d F Y');
        }

        if(is_null($dateFrom)) {
            $dateFrom = Carbon::now();
        }
        if(is_null($dateTo)) {
            $dateTo = Carbon::now();
        }

        $inProgressCount = OfficeallyTransaction::whereNotNull('start_posting_date')->count();

        $response = [
            'payments' => $dataset,
            'dateFrom' => $dateFrom->format('m/d/Y'),
            'dateTo' => $dateTo->format('m/d/Y'),
            'month' => $month,
            'selectedFilterType' => $request->filled('selected_filter_type') ? $request->selected_filter_type : 2,
            'in_progress_count' => $inProgressCount,
        ];

        return response()->json($response);
    }

    public function makePosting(MakePosting $request) {
        $transactionsBuilder = OfficeallyTransaction::select([
                'external_id',
                'patient_id',
                'payment_amount',
            ])
            ->whereNull('start_posting_date')
            ->where('applied_amount', '=', 0)
            ->whereIn('id', $request->payment_ids);

        $minMaxDate = (clone $transactionsBuilder)->select([
            DB::raw('MAX(DATE(transaction_date)) AS max_date'),
            DB::raw('MIN(DATE(transaction_date)) AS min_date'),
        ])->first();

        $transactions = $transactionsBuilder->with([
            'patient' => function($query) {
                $query->select([
                    'id'
                ]);
                $query->with([
                    'balance' => function($query) {
                        $query->select([
                            'balance_after_transaction AS balance',
                            'patient_id',
                        ]);
                    },
                ]);
            }
        ])->get();

        $transactionIds = [];
        $balances = [];

        $patientIds = $transactions->pluck('patient_id')->toArray();

        $patients = Patient::whereIn('id', $patientIds)
            ->select(['id'])
            ->with([
                'balance' => function($query) {
                    $query->select([
                        'patient_id',
                        'balance_after_transaction AS balance',
                    ]);
                },
                'officeallyTransactions' => function($query) {
                    $query->whereNotNull('start_posting_date');
                    $query->where('applied_amount', '=', 0);
                },
            ])
            ->get();

        foreach($patients as $patient) {
            if(!is_null($patient->balance)) {
                if (!key_exists($patient->id, $balances)) {
                    $balances[$patient->id] = $patient->balance->balance;
                }
            } else {
                $balances[$patient->id] = 0;
            }
            $patient = $patient->toArray();

            if(!is_null($patient['officeally_transactions'])) {
                foreach($patient['officeally_transactions'] as $transaction) {
                    $balances[$patient['id']] -= $transaction['payment_amount'];
                }
            }
        }

        foreach($transactions as $transaction) {
            if(!is_null($transaction->patient) && !is_null($transaction->patient->balance)) {
                if(!key_exists($transaction->patient_id, $balances)) {
                    $balances[$transaction->patient_id] = $transaction->patient->balance->balance;
                }
                if(($balances[$transaction->patient_id] - $transaction->payment_amount) >= 0) {
                    $balances[$transaction->patient_id] -= $transaction->payment_amount;
                    $transactionIds[] = $transaction->external_id;
                }
            }
        }

        OfficeallyTransaction::whereIn('external_id', $transactionIds)->update([
            'start_posting_date' => Carbon::now(),
        ]);
       
        if(!is_null($minMaxDate)) {
            $minDate = Carbon::parse($minMaxDate->min_date)->subDays(2);
            $maxDate = Carbon::parse($minMaxDate->max_date)->addDays(2);
            if($minDate === $maxDate) {
                $maxDate = null;
            }
            $job = new \App\Jobs\Officeally\MakePosting($transactionIds, $minDate, $maxDate);
            \Bus::dispatchNow($job);
            //dispatch(with(new \App\Jobs\Officeally\MakePosting($transactionIds, $minDate, $maxDate))->onQueue('officeally-billing'));
        }

        return [$transactionIds, $transactions];
    }

     /**
     * Get count of signed and submitted patient forms in JSON format.
     * @param Patient $patient
     * @return JsonResponse
     */
    public function getPreprocessedCount(Patient $patient): JsonResponse
    {
        $patientFormsCount = $this->preprocessedTransactionRepository->patientTransactionCount($patient); 
        return response()->json($patientFormsCount);
    }
}

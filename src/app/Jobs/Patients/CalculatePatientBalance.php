<?php

namespace App\Jobs\Patients;

use App\Models\Officeally\OfficeallyTransaction;
use App\Models\Patient\PatientPreprocessedTransaction;
use App\Models\Patient\PatientTransaction;
use App\Models\Patient\PatientTransactionAdjustment;
use App\Models\Square\SquareTransaction;
use App\Models\LateCancellationTransaction;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class CalculatePatientBalance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var array
     */
    private $patientIds;

    /**
     * Create a new job instance.
     *
     * @param array $patientIds
     */
    public function __construct(array $patientIds = [])
    {
        $this->patientIds = $patientIds;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $transactions = $this->getUnprocessedTransactions();
        $this->processTransactions($transactions);
        $transactions = $this->getUnpreprocessedTransactions();
        $this->processTransactions($transactions, true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|static[]
     */
    protected function getUnprocessedTransactions() {
        $officeAllyTransactions = OfficeallyTransaction::query()
            ->select([
                'id AS transactionable_id',
                'applied_amount AS amount_money',
                'patient_id',
                DB::raw("'" . quotemeta(OfficeallyTransaction::class) . "' AS transactionable_type"),
                'transaction_date',
            ])
            ->whereNull('processed_at');

        $squareTransactions = SquareTransaction::query()
            ->select([
                'square_transactions.id AS transactionable_id',
                'amount_money',
                'patient_square_accounts.patient_id',
                DB::raw("'" . quotemeta(SquareTransaction::class) . "' AS transactionable_type"),
                'transaction_date',
            ])
            ->whereNull('processed_at')
            ->join('patient_square_accounts', 'patient_square_accounts.id', '=', 'square_transactions.customer_id');

        $lateCancellationTransactions = LateCancellationTransaction::query()
            ->select([
                'late_cancellation_transactions.id AS transactionable_id',
                DB::raw('payment_amount * -1 AS amount_money'),
                'appointments.patients_id AS patient_id',
                DB::raw("'" . quotemeta(LateCancellationTransaction::class) . "' AS transactionable_type"),
                'transaction_date',
            ])
            ->whereNull('processed_at')
            ->join('appointments', 'appointments.id', '=', 'late_cancellation_transactions.appointment_id');

        if (count($this->patientIds)) {
            $officeAllyTransactions = $officeAllyTransactions->whereIn('patient_id', $this->patientIds);
            $squareTransactions = $squareTransactions->whereIn('patient_square_accounts.patient_id', $this->patientIds);
            $lateCancellationTransactions = $lateCancellationTransactions->whereIn('appointments.patients_id', $this->patientIds);
        } else {
            $squareTransactions = $squareTransactions->whereNotNull('patient_square_accounts.patient_id');
            $lateCancellationTransactions = $lateCancellationTransactions->whereNotNull('appointments.patients_id');
        }
        $officeAllyTransactions = $officeAllyTransactions->where('applied_amount', '<', 0);

        $transactions = $officeAllyTransactions
            ->union($squareTransactions)
            ->union($lateCancellationTransactions)
            ->orderBy('transaction_date')
            ->get();

        return $transactions;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|static[]
     */
    protected function getUnpreprocessedTransactions() {
        $officeAllyTransactions = OfficeallyTransaction::query()
            ->select([
                'id AS transactionable_id',
                DB::raw('payment_amount * -1 AS amount_money'),
                'patient_id',
                DB::raw("'" . quotemeta(OfficeallyTransaction::class) . "' AS transactionable_type"),
                'transaction_date',
            ])
            ->whereNull('preprocessed_at');

        $squareTransactions = SquareTransaction::query()
            ->select([
                'square_transactions.id AS transactionable_id',
                'amount_money',
                'patient_square_accounts.patient_id',
                DB::raw("'" . quotemeta(SquareTransaction::class) . "' AS transactionable_type"),
                'transaction_date',
            ])
            ->whereNull('preprocessed_at')
            ->join('patient_square_accounts', 'patient_square_accounts.id', '=', 'square_transactions.customer_id');

        $adjustmentTransactions = PatientTransactionAdjustment::query()
            ->select([
                'id AS transactionable_id',
                'amount AS amount_money',
                'patient_id',
                DB::raw("'" . quotemeta(PatientTransactionAdjustment::class) . "' AS transactionable_type"),
                'created_at AS transaction_date',
            ])
            ->whereNull('preprocessed_at');

        $lateCancellationTransactions = LateCancellationTransaction::query()
            ->select([
                'late_cancellation_transactions.id AS transactionable_id',
                DB::raw('payment_amount * -1 AS amount_money'),
                'appointments.patients_id AS patient_id',
                DB::raw("'" . quotemeta(LateCancellationTransaction::class) . "' AS transactionable_type"),
                'transaction_date',
            ])
            ->whereNull('preprocessed_at')
            ->join('appointments', 'appointments.id', '=', 'late_cancellation_transactions.appointment_id');

        if (count($this->patientIds)) {
            $officeAllyTransactions = $officeAllyTransactions->whereIn('patient_id', $this->patientIds);
            $squareTransactions = $squareTransactions->whereIn('patient_square_accounts.patient_id', $this->patientIds);
            $adjustmentTransactions = $adjustmentTransactions->whereIn('patient_id', $this->patientIds);
            $lateCancellationTransactions = $lateCancellationTransactions->whereIn('appointments.patients_id', $this->patientIds);
        } else {
            $squareTransactions = $squareTransactions->whereNotNull('patient_square_accounts.patient_id');
            $lateCancellationTransactions = $lateCancellationTransactions->whereNotNull('appointments.patients_id');
        }

        $transactions = $officeAllyTransactions
            ->union($squareTransactions)
            ->union($adjustmentTransactions)
            ->union($lateCancellationTransactions)
            ->orderBy('transaction_date')
            ->get();

        return $transactions;
    }

    /**
     * @param $transactions
     * @param bool $preprocess
     */
    protected function processTransactions($transactions, $preprocess = false) {
        foreach($transactions as $transaction) {
            if (!$preprocess) {
                $patientTransaction = PatientTransaction::getLast($transaction->patient_id);
            } else {
                $patientTransaction = PatientPreprocessedTransaction::getLast($transaction->patient_id);
            }
            $this->processTransaction($transaction, $patientTransaction, $preprocess);
        }
    }

    /**
     * @param $transaction
     * @param $patientTransaction
     * @param bool $preprocess
     *
     * @internal param $modelClassName
     */
    protected function processTransaction($transaction, $patientTransaction, $preprocess = false) {
        if (is_null($patientTransaction)) {
            $balanceBeforeTransaction = 0;
        } else {
            $balanceBeforeTransaction = $patientTransaction->balance_after_transaction;
        }
        $balanceAfterTransaction = $balanceBeforeTransaction + $transaction->amount_money;

        $transaction->balance_before_transaction = $balanceBeforeTransaction;
        $transaction->balance_after_transaction = $balanceAfterTransaction;
        unset($transaction->amount_money);
        unset($transaction->transaction_date);
        $modelClassName = PatientTransaction::class;
        if ($preprocess) {
            $modelClassName = PatientPreprocessedTransaction::class;
        }
        $modelClassName::firstOrCreate([
            'transactionable_id' => $transaction->transactionable_id,
            'transactionable_type' => $transaction->transactionable_type,
            'detached_at' => null,
        ], $transaction->toArray());
        if ($preprocess) {
            $this->setTransactionAsPreprocessed($transaction);
        } else {
            $this->setTransactionAsProcessed($transaction);
        }
    }

    /**
     * @param $transaction
     */
    protected function setTransactionAsProcessed($transaction) {
        $transactionableType = '\\' . $transaction->transactionable_type;
        $transactionableId = $transaction->transactionable_id;

        $transactionableType::query()
            ->where('id', '=', DB::raw($transactionableId))
            ->each(function ($transaction) {
                $transaction->update([
                    'processed_at' => Carbon::now(),
                ]);
            });
    }

    /**
     * @param $transaction
     */
    protected function setTransactionAsPreprocessed($transaction) {
        $transactionableType = '\\' . $transaction->transactionable_type;
        $transactionableId = $transaction->transactionable_id;

        $transactionableType::query()
            ->where('id', '=', DB::raw($transactionableId))
            ->each(function ($transaction) {
                $transaction->update([
                    'preprocessed_at' => Carbon::now(),
                ]);
            });
    }
}

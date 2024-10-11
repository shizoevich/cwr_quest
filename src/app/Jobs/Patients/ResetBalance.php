<?php

namespace App\Jobs\Patients;

use App\Models\Officeally\OfficeallyTransaction;
use App\Models\Patient\PatientTransaction;
use App\Models\Patient\PatientTransactionAdjustment;
use App\Models\Square\SquareTransaction;
use App\Patient;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class ResetBalance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Carbon
     */

    private $endDate;
    /**
     * @var
     */
    private $patientId;

    /**
     * Create a new job instance.
     *
     * @param Carbon $endDate
     * @param int|null $patientId
     */
    public function __construct(Carbon $endDate, int $patientId = null)
    {
        $this->endDate = $endDate;
        $this->patientId = $patientId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $patients = $this->getPatients();
        foreach ($patients as $patient) {
            $lastTransaction = $this->getLastTransaction($patient);
            if(!is_null($lastTransaction)) {
                $transaction = PatientTransaction::query()
                    ->where('transactionable_type', $lastTransaction->model)
                    ->where('transactionable_id', $lastTransaction->id)
                    ->first();
                if($transaction->balance_after_transaction >= 0) {
                    continue;
                }
                $currentPatientBalance = PatientTransaction::getLast($patient->id);
                if($transaction->balance_after_transaction < 0) {
                    if($currentPatientBalance->balance_after_transaction < 0 && $currentPatientBalance->balance_after_transaction + abs($transaction->balance_after_transaction) <= 0) {
                        $now = Carbon::now();
                        $adjustment = $patient->transactionAdjustments()->create([
                            'amount' => abs($transaction->balance_after_transaction),
                            'transaction_date' => $now,
                            'processed_at' => $now,
                            'comment' => 'Submitted by System',
                        ]);
                        $adjustment->patientTransaction()->create([
                            'patient_id' => $patient->id,
                            'balance_before_transaction' => $currentPatientBalance->balance_after_transaction,
                            'balance_after_transaction' => $currentPatientBalance->balance_after_transaction + ($transaction->balance_after_transaction * -1),
                        ]);
                    }
                }
            }
        }
    }

    private function getLastTransaction($patient) {
        $oaTransactions = $patient->officeallyTransactions()
            ->select([
                'id',
                'patient_id',
                DB::raw("NULL AS customer_id"),
                'transaction_date',
                DB::raw("'" . addslashes(OfficeallyTransaction::class) . "'" . ' AS model'),
            ])
            ->whereNotNull('processed_at')
            ->whereDate('transaction_date', '<=', $this->endDate);
        $adjustmentTransactions = $patient->transactionAdjustments()
            ->select([
                'id',
                'patient_id',
                DB::raw("NULL AS customer_id"),
                'transaction_date',
                DB::raw("'" . addslashes(PatientTransactionAdjustment::class) . "'" . ' AS model'),
            ])
            ->whereNotNull('processed_at')
            ->whereDate('transaction_date', '<=', $this->endDate);
        $transactions = $oaTransactions->union($adjustmentTransactions);
        $customerIds = $patient->squareAccounts()
            ->pluck('id');
        if(count($customerIds)) {
            $sqTransactions = SquareTransaction::query()
                ->select([
                    'id',
                    DB::raw("NULL AS patient_id"),
                    'customer_id',
                    'transaction_date',
                    DB::raw("'" . addslashes(SquareTransaction::class) . "'" . ' AS model'),
                ])
                ->whereNotNull('processed_at')
                ->whereIn('customer_id', $customerIds)
                ->whereDate('transaction_date', '<=', $this->endDate);
            $transactions = $transactions->union($sqTransactions);
        }

        return $transactions->orderBy('transaction_date', 'desc')
            ->first();
    }

    /**
     * @return mixed
     */
    private function getPatients($offset = 0)
    {
        $patients = Patient::query()
            ->select(['id',])
            ->when($this->patientId > 0, function ($query) {
                $query->where('id', $this->patientId);
            })->with([
                'officeallyTransactions' => function ($query) {
                    $query->whereDate('transaction_date', '<=', $this->endDate);
                    $query->orderBy('transaction_date', 'desc');
                    $query->select(['id', 'patient_id', 'transaction_date']);
                    $query->limit(1);
                    $query->whereNotNull('processed_at');
                },
                'transactionAdjustments' => function ($query) {
                    $query->whereDate('transaction_date', '<=', $this->endDate);
                    $query->orderBy('transaction_date', 'desc');
                    $query->select(['id', 'patient_id', 'transaction_date']);
                    $query->limit(1);
                    $query->whereNotNull('processed_at');
                },
                'squareAccounts.transactions' => function ($query) {
                    $query->whereDate('transaction_date', '<=', $this->endDate);
                    $query->orderBy('transaction_date', 'desc');
                    $query->select(['id', 'customer_id', 'transaction_date']);
                    $query->limit(1);
                    $query->whereNotNull('processed_at');
                }
            ])->whereHas('transactions')
            ->get();


        $patients = Patient::query()
            ->when($this->patientId > 0, function ($query) {
                $query->where('id', $this->patientId);
            })->whereHas('transactions')
            ->get();

        return $patients;
    }
}

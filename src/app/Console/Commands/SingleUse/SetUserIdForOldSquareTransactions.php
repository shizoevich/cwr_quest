<?php

namespace App\Console\Commands\SingleUse;

use App\Models\Square\SquareLog;
use App\Models\Square\SquareTransaction;
use App\PatientSquareAccount;
use Illuminate\Console\Command;

class SetUserIdForOldSquareTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'square:set-user-for-old-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        SquareTransaction::query()
            ->whereNull('user_id')
            ->orderBy('id', 'desc')
            ->chunk(1000, function ($transactions) {
                foreach ($transactions as $transaction) {
                    $account = PatientSquareAccount::where('id', '=', $transaction->customer_id)->first();
                    if ($account) {
                        $log = SquareLog::query()
                            ->where('is_success', 1)
                            ->where('action', SquareLog::ACTION_CREATE_PAYMENT)
                            ->where('request', 'like', '%"customer_id":"' . $account->external_id . '"%')
                            ->where('request', 'like', '%"amount":' . $transaction->amount_money . ',%')
                            ->whereDate('created_at', $transaction->transaction_date->toDateString())
                            ->first();

                        if ($log) {
                            // dump("Transaction ID {$transaction->id} updated with User ID {$log->user_id}");
                            $transaction->update(['user_id' => $log->user_id]);
                        }
                    }
                }
            });
    }
}

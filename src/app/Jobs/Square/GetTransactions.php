<?php

namespace App\Jobs\Square;

use App\Models\Square\SquareCardBrand;
use App\Models\Square\SquareLocation;
use App\Models\Square\SquareTransaction;
use App\Models\Square\SquareTransactionEntryMethod;
use App\Models\Square\SquareTransactionType;
use App\Patient;
use App\PatientSquareAccount;
use App\Repositories\Square\ApiRepositoryInterface as SquareApiRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Square\Models\Transaction;

/**
 * Class GetTransactions
 * @package App\Jobs\Square
 */
class GetTransactions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /** @var SquareApiRepositoryInterface */
    private $squareApi;
    
    /** @var Carbon */
    private $beginTime;
    
    /** @var Carbon */
    private $endTime;
    
    /** @var string */
    private $sortOrder;
    
    /**
     * GetTransactions constructor.
     *
     * @param SquareApiRepositoryInterface $squareApi
     * @param Carbon                       $beginTime
     * @param Carbon                       $endTime
     * @param string                       $sortOrder
     */
    public function __construct(Carbon $beginTime, Carbon $endTime, $sortOrder = 'ASC')
    {
        $this->squareApi = app()->make(SquareApiRepositoryInterface::class);
        $this->beginTime = $beginTime;
        $this->endTime = $endTime;
        $this->sortOrder = $sortOrder;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $locationIds = SquareLocation::getIds();
        $cursor = null;
        foreach($locationIds as $locationId) {
            while(true) {
                $response = $this->squareApi->getPayments($this->beginTime, $this->endTime, $locationId, $cursor);
                $transactions = optional($response->getResult())->getTransactions();
                if(!is_null($transactions)) {
                    $this->saveTransactions($transactions);
                }
                $cursor = $response->getCursor();
                if(is_null($cursor)) {
                    break;
                }
            }
        }
    }
    
    /**
     * @param Transaction[] $transactions
     */
    protected function saveTransactions($transactions) {
        foreach($transactions as $transaction) {
            $location = SquareLocation::firstOrCreate([
                'external_id' => $transaction->getLocationId(),
            ]);
            $tender = $transaction->getTenders()[0];
            $customerId = $tender->getCustomerId();
            if(is_null($customerId)) {
               continue;
            }
            
            \DB::beginTransaction();
            $customer = PatientSquareAccount::firstOrCreate([
                'external_id' => $customerId,
            ]);

            try {
                if(is_null($customer->patient_id)) {
                    $squareCustomer = $this->squareApi->getCustomer($customerId);
                    $firstName = $squareCustomer->getGivenName();
                    $lastName = $squareCustomer->getFamilyName();
                    $patient = Patient::where('first_name', $firstName)
                        ->where('last_name', $lastName)
                        ->whereDoesntHave('squareAccount')
                        ->first();
                    if(!is_null($patient)) {
                        $customer->patient_id = $patient->id;
                    }
                    $customer->first_name = $firstName;
                    $customer->last_name = $lastName;
                    $customer->email = $squareCustomer->getEmailAddress();
                    $customer->save();
                }
                \DB::commit();
            } catch(\Exception $e) {
                \DB::rollBack();
                \Log::error($e->getTraceAsString());
                \App\Helpers\SentryLogger::captureException($e);
            }


            $transactionType = SquareTransactionType::firstOrCreate([
                'name' => $tender->getType(),
            ]);

            $cardDetails = $tender->getCardDetails();
            $entryMethod = null;
            $cardBrand = null;
            $cardLastFour = null;
            if(!is_null($cardDetails)) {
                $entryMethod = $cardDetails->getEntryMethod();
                $card = $cardDetails->getCard();
                if(!is_null($card)) {
                    $cardBrand = $card->getCardBrand();
                    $cardBrand = SquareCardBrand::firstOrCreate([
                        'name' => $cardBrand,
                    ]);
                    $cardLastFour = $card->getLast4();
                }
            }

            if(!is_null($entryMethod)) {
                $entryMethod = SquareTransactionEntryMethod::firstOrCreate([
                    'name' => $cardDetails->getEntryMethod(),
                ]);
            }

            $transactionDate = $transaction->getCreatedAt();

            $transactionDate = Carbon::parse($transactionDate)->setTimezone(config('app.timezone'));

            SquareTransaction::updateOrCreate([
                'external_id' => $tender->getId(),
            ], [
                'location_id' => $location->id,
                'customer_id' => $customer->id,
                'transaction_type_id' => $transactionType->id,
                'amount_money' => $tender->getAmountMoney()->getAmount(),
                'card_brand_id' => optional($cardBrand)->id,
                'card_last_four' => $cardLastFour,
                'entry_method_id' => optional($entryMethod)->id,
                'transaction_date' => $transactionDate,
            ]);
        }
    }
}

<?php

namespace App\Repositories\Appointment\Payment;

use App\Components\Square\Customer;
use App\Jobs\Patients\CalculatePatientBalance;
use App\Models\Square\SquareCardBrand;
use App\Models\Square\SquareLocation;
use App\Models\Square\SquareOrder;
use App\Models\Square\SquareCatalogItem;
use App\Models\Square\SquareTransaction;
use App\Models\Square\SquareTransactionEntryMethod;
use App\Models\Square\SquareTransactionType;
use App\PatientSquareAccountCard;
use App\Repositories\Square\ApiRepositoryInterface;
use App\Exceptions\Square\SquareException;
use Square\Exceptions\ApiException;
use Carbon\Carbon;

/**
 * Class CreditCardPaymentRepository
 * @package App\Repositories\Appointment\Payment
 */
class CreditCardPaymentRepository extends AbstractPaymentRepository
{
    /**
     * @inheritDoc
     */
    public function pay(): bool
    {
        if (isset($this->payload['card_id'])) {
            $creditCard = PatientSquareAccountCard::findOrFail($this->payload['card_id']);
            $sourceId = $creditCard->card_id;
            $customer = $creditCard->account;
        } else if (isset($this->payload['card_nonce'])) {
            $sourceId = $this->payload['card_nonce'];
            $customerService = new Customer();
            $customer = $customerService->createIfNotExist($this->patient);
        } else {
            return false;
        }
        
        $squareApi = app()->make(ApiRepositoryInterface::class);
        
        $order = null;
        if (isset($this->payload['catalog_item_id'])) {
            $order = $this->createOrder($customer, $this->payload['catalog_item_id'], $squareApi);
        }

        try {
            $this->createPayment($customer, $sourceId, $order, $squareApi);
        } catch (ApiException | SquareException $e) {
            if (isset($order)) {
                $this->cancelOrder($order, $squareApi);
            }

            throw $e;
        }

        \Bus::dispatchNow(new CalculatePatientBalance([$this->patient->getKey()]));

        return true;
    }

    protected function createOrder($customer, $catalogItemId, $squareApi = null)
    {
        if (empty($squareApi)) {
            $squareApi = app()->make(ApiRepositoryInterface::class);
        }

        $catalogItem = SquareCatalogItem::find($catalogItemId);
        $lineItem = [
            'name' => $catalogItem->name,
            'catalog_object_id' => $catalogItem->external_id,
            'cost' => $this->payload['amount'],
            'quantity' => 1,
        ];
        $squareOrder = $squareApi->createOrder($customer->external_id, config('square.location_id'), [$lineItem]);
        
        return $this->saveSquareOrder($squareOrder, $customer, $catalogItem);
    }

    protected function saveSquareOrder($squareOrder, $customer, $catalogItem)
    {
        $orderDate = $squareOrder->getCreatedAt();
        $orderDate = Carbon::parse($orderDate)->setTimezone(config('app.timezone'));
        $location = SquareLocation::firstOrCreate([
            'external_id' => $squareOrder->getLocationId(),
        ]);

        return SquareOrder::updateOrCreate([
            'external_id' => $squareOrder->getId(),
        ], [
            'location_id' => $location->id,
            'customer_id' => $customer->id,
            'catalog_item_id' => $catalogItem->id,
            'amount_money' => $squareOrder->getTotalMoney()->getAmount(),
            'order_date' => $orderDate,
        ]);
    }

    protected function cancelOrder($order, $squareApi = null)
    {
        if (empty($squareApi)) {
            $squareApi = app()->make(ApiRepositoryInterface::class);
        }

        $squareApi->cancelOrder($order->external_id, config('square.location_id'));
        $order->delete();
    }

    protected function createPayment($customer, $sourceId, $order = null, $squareApi = null)
    {
        if (empty($squareApi)) {
            $squareApi = app()->make(ApiRepositoryInterface::class);
        }

        $squarePayment = $squareApi->createPayment($customer->external_id, $sourceId, $this->payload['amount'], optional($order)->external_id);

        return $this->saveSquareTransaction($squarePayment, $customer, $order);
    }

    protected function saveSquareTransaction($squarePayment, $customer, $order = null)
    {
        $transactionType = SquareTransactionType::firstOrCreate([
            'name' => $squarePayment->getSourceType(),
        ]);
    
        $cardDetails = $squarePayment->getCardDetails();
        $cardBrand = null;
        $cardLastFour = null;
        $entryMethod = null;

        if (!is_null($cardDetails)) {
            $card = $cardDetails->getCard();
            $entryMethod = $cardDetails->getEntryMethod();

            if (!is_null($card)) {
                $cardBrand = $card->getCardBrand();
                $cardBrand = SquareCardBrand::firstOrCreate([
                    'name' => $cardBrand,
                ]);
                $cardLastFour = $card->getLast4();
            }
        }
    
        if (!is_null($entryMethod)) {
            $entryMethod = SquareTransactionEntryMethod::firstOrCreate([
                'name' => $cardDetails->getEntryMethod(),
            ]);
        }
    
        $transactionDate = $squarePayment->getCreatedAt();
        $transactionDate = Carbon::parse($transactionDate)->setTimezone(config('app.timezone'));
        $location = SquareLocation::firstOrCreate([
            'external_id' => $squarePayment->getLocationId(),
        ]);

        return SquareTransaction::updateOrCreate([
            'external_id' => $squarePayment->getId(),
        ], [
            'external_id' => $squarePayment->getId(),
            'location_id' => $location->id,
            'customer_id' => $customer->id,
            'transaction_type_id' => $transactionType->id,
            'amount_money' => $squarePayment->getTotalMoney()->getAmount(),
            'card_brand_id' => optional($cardBrand)->id,
            'card_last_four' => $cardLastFour,
            'entry_method_id' => optional($entryMethod)->id,
            'order_id' => optional($order)->id,
            'appointment_id' => $this->payload['appointment_id'] ?? null,
            'user_id' => $this->payload['user_id'] ?? null,
            'transaction_date' => $transactionDate,
        ]);
    }
    
    /**
     * @inheritDoc
     */
    protected function getOfficeAllyPaymentMethod(): int
    {
        return 3;
    }
}
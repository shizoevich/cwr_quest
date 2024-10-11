<?php

namespace App\Repositories\Square\Traits;

use App\Components\Square\SquareClient;
use App\Exceptions\Square\SquareException;
use App\Models\Square\SquareLog;
use Carbon\Carbon;
use Log;
use Square\Exceptions\ApiException;
use Square\Models\CreatePaymentRequest;
use Square\Models\Currency;
use Square\Models\Money;
use Uuid;

/**
 * Trait Payment
 * @package App\Repositories\Square\Traits
 */
trait Payment
{
    /**
     * @inheritDoc
     */
    public function getPayments(
        Carbon $beginTime,
        Carbon $endTime,
        string $locationId,
        $cursor = null,
        $sortOrder = 'ASC'
    ) {
        /** @var SquareClient $client */
        $client = $this->getClient();
        
        /**
         * $beginTime->toRfc3339String() - square returns error
         */
        return $client->getTransactionsApi()->listTransactions($locationId, $beginTime->format('Y-m-d\TH:i:s\Z'), $endTime->format('Y-m-d\TH:i:s\Z'), $sortOrder, $cursor);
    }
    
    /**
     * @inheritDoc
     */
    public function getPayment(string $paymentId)
    {
        /** @var SquareClient $client */
        $client = $this->getClient();
        
        return optional($client->getPaymentsApi()->getPayment($paymentId)->getResult())->getPayment();
    }
    
    /**
     * @inheritDoc
     */
    public function createPayment(
        string $customerId,
        string $sourceId,
        int $amountMoney,
        string $orderId = null,
        string $idempotencyKey = null,
        int $retryCount = 1
    ) {
        if (!$idempotencyKey) {
            $idempotencyKey = Uuid::generate(4)->string;
        }
        
        $money = new Money();
        $money->setAmount($amountMoney);
        $money->setCurrency(Currency::USD);
    
        $body = new CreatePaymentRequest(
            $sourceId,
            $idempotencyKey,
            $money
        );
        /**
         * charge immediately instead preauthorization
         */
        $body->setAutocomplete(true);
        $body->setCustomerId($customerId);

        if (isset($orderId)) {
            $body->setOrderId($orderId);
        }
    
        /** @var SquareClient $client */
        $client = $this->getClient();

        try {
            $response = $client->getPaymentsApi()->createPayment($body);
        } catch (ApiException $e) {
            if ($retryCount > 0) {
                Log::error($e->getMessage());
            
                return $this->createPayment($customerId, $sourceId, $amountMoney, $orderId, $idempotencyKey, --$retryCount);
            }
            $this->writeErrorLog(SquareLog::ACTION_CREATE_PAYMENT, $body->jsonSerialize(), (array)$e->getHttpResponse());
            throw $e;
        }

        if ($response->isSuccess()) {
            $response = $response->getResult()->getPayment();
            $this->writeSuccessLog(SquareLog::ACTION_CREATE_PAYMENT, $body->jsonSerialize(), $response->jsonSerialize());
            
            return $response;
        }

        $this->writeErrorLog(SquareLog::ACTION_CREATE_PAYMENT, $body->jsonSerialize(), $response->getErrors());
        throw new SquareException($response->getErrors());
    }
}
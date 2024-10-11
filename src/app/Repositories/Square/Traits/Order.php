<?php

namespace App\Repositories\Square\Traits;

use App\Exceptions\Square\SquareException;
use App\Models\Square\SquareLog;
use Log;
use RuntimeException;
use Square\Exceptions\ApiException;
use Square\Models\CreateOrderRequest;
use Square\Models\UpdateOrderRequest;
use Square\Models\Currency;
use Square\Models\Money;
use Square\Models\OrderLineItem;
use Square\Models\OrderSource;
use Square\SquareClient;
use App\Helpers\Constant\SquareConst;
use Uuid;

/**
 * Trait Order
 * @package App\Repositories\Square\Traits
 */
trait Order
{
    /**
     * @inheritDoc
     */
    public function createOrder(
        string $customerId,
        string $locationId,
        array $lineItems,
        string $idempotencyKey = null,
        int $retryCount = 1
    ) {
        if (empty($lineItems)) {
            throw new RuntimeException("lineItems can't be empty.");
        }
        
        if (!$idempotencyKey) {
            $idempotencyKey = Uuid::generate(4)->string;
        }
        
        $body = new CreateOrderRequest();
        $body->setIdempotencyKey($idempotencyKey);
        
        $order = new \Square\Models\Order($locationId);
        
        $orderSource = new OrderSource();
        $orderSource->setName('Change Within Reach');
        $order->setSource($orderSource);
        
        $orderLineItems = [];
        foreach ($lineItems as $lineItem) {
            $orderLineItem = new OrderLineItem($lineItem['quantity']);
            if (array_key_exists('catalog_object_id', $lineItem)) {
                $orderLineItem->setCatalogObjectId($lineItem['catalog_object_id']);
            } else if (array_key_exists('name', $lineItem)) {
                $orderLineItem->setName($lineItem['name']);
            } else {
                throw new RuntimeException('catalog_object_id or name is required');
            }
            
            if (array_key_exists('note', $lineItem)) {
                $orderLineItem->setNote($lineItem['note']);
            }

            $cost = new Money();
            $cost->setAmount($lineItem['cost']);
            $cost->setCurrency(Currency::USD);
            $orderLineItem->setBasePriceMoney($cost);
            
            $orderLineItems[] = $orderLineItem;
        }
        
        $order->setLineItems($orderLineItems);
        $order->setCustomerId($customerId);
        $body->setOrder($order);

        /** @var SquareClient $client */
        $client = $this->getClient();

        try {
            $response = $client->getOrdersApi()->createOrder($body);
        } catch (ApiException $e) {
            if ($retryCount > 0) {
                Log::error($e->getMessage());
                
                return $this->createOrder($customerId, $locationId, $lineItems, $idempotencyKey, --$retryCount);
            }
            $this->writeErrorLog(SquareLog::ACTION_CREATE_ORDER, $body->jsonSerialize(), (array)$e->getHttpResponse());
            throw $e;
        }
        
        if ($response->isSuccess()) {
            $response = $response->getResult()->getOrder();
            $this->writeSuccessLog(SquareLog::ACTION_CREATE_ORDER, $body->jsonSerialize(), $response->jsonSerialize());
    
            return $response;
        }

        $this->writeErrorLog(SquareLog::ACTION_CREATE_ORDER, $body->jsonSerialize(), $response->getErrors());
        throw new SquareException($response->getErrors());
    }
    
    /**
     * @inheritDoc
     */
    public function getOrder(string $orderId, int $retryCount = 1)
    {
        /** @var SquareClient $client */
        $client = $this->getClient();
        
        try {
            $response = $client->getOrdersApi()->retrieveOrder($orderId);
        } catch (ApiException $e) {
            if ($retryCount > 0) {
                Log::error($e->getMessage());
                
                return $this->getOrder($orderId, --$retryCount);
            }
            $this->writeErrorLog(SquareLog::ACTION_GET_ORDER, ['order_id' => $orderId], (array)$e->getHttpResponse());
            throw $e;
        }

        if ($response->isSuccess()) {
            return $response->getResult()->getOrder();
        }

        $this->writeErrorLog(SquareLog::ACTION_GET_ORDER, ['order_id' => $orderId], $response->getErrors());
        throw new SquareException($response->getErrors());
    }

    /**
     * @inheritDoc
     */
    public function cancelOrder(string $orderId, string $locationId, int $retryCount = 1)
    {
        $squareOrder = $this->getOrder($orderId);

        $body = new UpdateOrderRequest();
        $order = new \Square\Models\Order($locationId);
        
        $order->setVersion($squareOrder->getVersion());
        $order->setState(SquareConst::ORDER_CANCELED_STATE);
        $body->setOrder($order);

        /** @var SquareClient $client */
        $client = $this->getClient();

        try {
            $response = $client->getOrdersApi()->updateOrder($orderId, $body);
        } catch (ApiException $e) {
            if ($retryCount > 0) {
                Log::error($e->getMessage());
                
                return $this->cancelOrder($orderId, $locationId, --$retryCount);
            }
            $this->writeErrorLog(SquareLog::ACTION_CANCEL_ORDER, $body->jsonSerialize(), (array)$e->getHttpResponse());
            throw $e;
        }
        
        if ($response->isSuccess()) {
            $response = $response->getResult()->getOrder();
            $this->writeSuccessLog(SquareLog::ACTION_CANCEL_ORDER, $body->jsonSerialize(), $response->jsonSerialize());
    
            return $response;
        }

        $this->writeErrorLog(SquareLog::ACTION_CANCEL_ORDER, $body->jsonSerialize(), $response->getErrors());
        throw new SquareException($response->getErrors());
    }
}
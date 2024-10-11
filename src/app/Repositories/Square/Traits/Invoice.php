<?php

namespace App\Repositories\Square\Traits;

use App\Exceptions\Square\SquareException;
use App\Models\Square\SquareLog;
use Carbon\Carbon;
use Log;
use Square\Exceptions\ApiException;
use Square\Models\CreateInvoiceRequest;
use Square\Models\InvoicePaymentRequest;
use Square\Models\InvoiceRecipient;
use Square\Models\InvoiceRequestMethod;
use Square\Models\InvoiceRequestType;
use Square\Models\PublishInvoiceRequest;
use Square\SquareClient;
use Uuid;

/**
 * Trait Invoice
 * @package App\Repositories\Square\Traits
 */
trait Invoice
{
    /**
     * @inheritDoc
     */
    public function createInvoice(
        string $customerId,
        string $locationId,
        string $orderId,
        string $idempotencyKey = null,
        int $retryCount = 1
    ) {
        if (!$idempotencyKey) {
            $idempotencyKey = Uuid::generate(4)->string;
        }
        $body = new \Square\Models\Invoice();
        $body->setLocationId($locationId);
        $body->setOrderId($orderId);
        
        $recipient = new InvoiceRecipient();
        $recipient->setCustomerId($customerId);
        $body->setPrimaryRecipient($recipient);
        
        $paymentRequest = new InvoicePaymentRequest();
        $paymentRequest->setRequestMethod(InvoiceRequestMethod::EMAIL);
        $paymentRequest->setRequestType(InvoiceRequestType::BALANCE);
        $paymentRequest->setDueDate(Carbon::today()->addWeek()->toDateString());
        $body->setPaymentRequests([$paymentRequest]);
        
        $body = new CreateInvoiceRequest($body);
        $body->setIdempotencyKey($idempotencyKey);
        /** @var SquareClient $client */
        $client = $this->getClient();
        try {
            $response = $client->getInvoicesApi()->createInvoice($body);
        } catch (ApiException $e) {
            if ($retryCount > 0) {
                Log::error($e->getMessage());
                
                return $this->createInvoice($customerId, $locationId, $orderId, $idempotencyKey, --$retryCount);
            }
            $this->writeErrorLog(SquareLog::ACTION_CREATE_INVOICE, $body->jsonSerialize(), (array)$e->getHttpResponse());
            throw $e;
        }
        
        if ($response->isSuccess()) {
            $response = $response->getResult()->getInvoice();
            $this->writeSuccessLog(SquareLog::ACTION_CREATE_INVOICE, $body->jsonSerialize(), $response->jsonSerialize());
            
            return $response;
        }
        $this->writeErrorLog(SquareLog::ACTION_CREATE_INVOICE, $body->jsonSerialize(), $response->getErrors());
        throw new SquareException($response->getErrors());
    }
    
    /**
     * @inheritDoc
     */
    public function publishInvoice(
        string $invoiceId,
        string $version,
        string $idempotencyKey = null,
        int $retryCount = 1
    ) {
        if (!$idempotencyKey) {
            $idempotencyKey = Uuid::generate(4)->string;
        }
        $body = new PublishInvoiceRequest($version);
        $body->setIdempotencyKey($idempotencyKey);
        /** @var SquareClient $client */
        $client = $this->getClient();
        try {
            $response = $client->getInvoicesApi()->publishInvoice($invoiceId, $body);
        } catch (ApiException $e) {
            if ($retryCount > 0) {
                Log::error($e->getMessage());
                
                return $this->publishInvoice($invoiceId, $version, $idempotencyKey, --$retryCount);
            }
            $this->writeErrorLog(SquareLog::ACTION_PUBLISH_INVOICE, $body->jsonSerialize(), (array)$e->getHttpResponse());
            throw $e;
        }
        
        if ($response->isSuccess()) {
            $response = $response->getResult()->getInvoice();
            $this->writeSuccessLog(SquareLog::ACTION_PUBLISH_INVOICE, $body->jsonSerialize(), $response->jsonSerialize());
    
            return $response;
        }
        $this->writeErrorLog(SquareLog::ACTION_PUBLISH_INVOICE, $body->jsonSerialize(), $response->getErrors());
        throw new SquareException($response->getErrors());
    }
}
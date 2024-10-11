<?php

namespace App\Repositories\Square;

use App\Exceptions\Square\SquareException;
use Carbon\Carbon;
use Exception;
use Square\Exceptions\ApiException;
use Square\Http\ApiResponse;
use Square\Models\Card;
use Square\Models\Customer;
use Square\Models\Invoice;
use Square\Models\Location;
use Square\Models\CatalogObject;
use Square\Models\Order;
use Square\Models\Payment;
use Square\SquareClient;

/**
 * Interface ApiRepositoryInterface
 * @package App\Repositories\Square
 */
interface ApiRepositoryInterface
{
    /**
     * @return SquareClient
     */
    public function getClient(): SquareClient;
    
    /**
     * @param string $action
     * @param array  $request
     * @param array  $response
     * @param bool   $isSuccess
     *
     * @return mixed
     */
    public function writeLog(string $action, array $request, array $response, bool $isSuccess);
    
    /**
     * @param string $action
     * @param array  $request
     * @param array  $response
     *
     * @return mixed
     */
    public function writeErrorLog(string $action, array $request, array $response);
    
    /**
     * @param string $action
     * @param array  $request
     * @param array  $response
     *
     * @return mixed
     */
    public function writeSuccessLog(string $action, array $request, array $response);
    
    /**
     * @return Location[]
     * @throws ApiException
     */
    public function getLocations();

    /**
     * @return CatalogObject[]
     * @throws ApiException
     */
    public function getCatalogObjects();
    
    /**
     * @param Carbon $beginTime
     * @param Carbon $endTime
     * @param string $locationId
     * @param null   $cursor
     * @param string $sortOrder
     *
     * @return ApiResponse
     * @throws ApiException
     */
    public function getPayments(
        Carbon $beginTime,
        Carbon $endTime,
        string $locationId,
        $cursor = null,
        $sortOrder = 'ASC'
    );
    
    /**
     * @param string $paymentId
     *
     * @return Payment|null
     * @throws ApiException
     */
    public function getPayment(string $paymentId);
    
    /**
     * @param string $customerId
     *
     * @return Customer|null
     * @throws ApiException
     */
    public function getCustomer(string $customerId);
    
    /**
     * @param array       $patientInfo
     *
     * @param string|null $idempotencyKey
     * @param int         $retryCount
     *
     * @return Customer
     * @throws ApiException
     * @throws Exception
     */
    public function createCustomer(array $patientInfo, string $idempotencyKey = null, int $retryCount = 1);
    
    /**
     * @param string      $customerId
     * @param array       $patientInfo
     * @param int         $retryCount
     *
     * @return mixed
     */
    public function updateCustomer(string $customerId, array $patientInfo, int $retryCount = 1);
    
    /**
     * @param string $customerId
     * @param string $creditCardNonce
     * @param        $zip
     * @param array  $additionalData
     * @param int    $retryCount
     *
     * @return Card
     * @throws ApiException
     * @throws SquareException
     */
    public function createCustomerCard(
        string $customerId,
        string $creditCardNonce,
        $zip = null,
        array $additionalData = [],
        int $retryCount = 1
    );
    
    /**
     * @param string      $customerId
     * @param string      $locationId
     * @param array       $lineItems
     * @param string|null $idempotencyKey
     * @param int         $retryCount
     *
     * @return Order
     * @throws SquareException
     * @throws ApiException
     * @throws Exception
     */
    public function createOrder(
        string $customerId,
        string $locationId,
        array $lineItems,
        string $idempotencyKey = null,
        int $retryCount = 1
    );
    
    /**
     * @param string $orderId
     *
     * @return Order
     * @throws SquareException
     * @throws ApiException
     * @throws Exception
     */
    public function getOrder(string $orderId);

    /**
     * @param string      $orderId
     * @param string      $locationId
     * @param int         $retryCount
     *
     * @return Order
     * @throws SquareException
     * @throws ApiException
     * @throws Exception
     */
    public function cancelOrder(string $orderId, string $locationId, int $retryCount = 1);
    
    /**
     * @param string      $customerId
     * @param string      $locationId
     * @param string      $orderId
     * @param string|null $idempotencyKey
     * @param int         $retryCount
     *
     * @return Invoice
     * @throws SquareException
     * @throws ApiException
     * @throws Exception
     */
    public function createInvoice(
        string $customerId,
        string $locationId,
        string $orderId,
        string $idempotencyKey = null,
        int $retryCount = 1
    );
    
    /**
     * @param string      $invoiceId
     * @param string      $version
     *
     * @param string|null $idempotencyKey
     * @param int         $retryCount
     *
     * @return Invoice
     * @throws SquareException
     * @throws ApiException
     * @throws Exception
     */
    public function publishInvoice(
        string $invoiceId,
        string $version,
        string $idempotencyKey = null,
        int $retryCount = 1
    );
    
    /**
     * @param string      $customerId
     * @param string      $sourceId
     * @param int         $amountMoney
     * @param string|null $orderId
     * @param string|null $idempotencyKey
     * @param int         $retryCount
     *
     * @return Payment
     * @throws ApiException
     * @throws SquareException
     */
    public function createPayment(
        string $customerId,
        string $sourceId,
        int $amountMoney,
        string $orderId = null,
        string $idempotencyKey = null,
        int $retryCount = 1
    );
    
}
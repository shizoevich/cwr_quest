<?php

namespace App\Repositories\Square\Traits;

use App\Exceptions\Square\SquareException;
use App\Models\Square\SquareLog;
use Log;
use Square\Exceptions\ApiException;
use Square\Models\Address;
use Square\Models\Country;
use Square\Models\CreateCustomerCardRequest;

/**
 * Trait CustomerCard
 * @package App\Repositories\Square\Traits
 */
trait CustomerCard
{
    /**
     * @inheritDoc
     */
    public function createCustomerCard(
        string $customerId,
        string $creditCardNonce,
        $zip = null,
        array $additionalData = [],
        int $retryCount = 1
    ) {
        $body = new CreateCustomerCardRequest($creditCardNonce);
        
        $address = new Address();
        $address->setPostalCode($zip);
        $address->setCountry(Country::US);
        if (isset($additionalData['address_line_1'])) {
            $address->setAddressLine1($additionalData['address_line_1']);
        }
        if (isset($additionalData['city'])) {
            $address->setLocality($additionalData['city']);
        }
        if (isset($additionalData['state'])) {
            $address->setAdministrativeDistrictLevel1($additionalData['state']);
        }
        $body->setBillingAddress($address);
        $client = $this->getClient();
        try {
            $response = $client->getCustomersApi()->createCustomerCard($customerId, $body);
        } catch (ApiException $e) {
            if ($retryCount > 0) {
                Log::error($e->getMessage());
                
                return $this->createCustomerCard($customerId, $creditCardNonce, $zip, $additionalData, --$retryCount);
            }
            $this->writeErrorLog(SquareLog::ACTION_CREATE_CUSTOMER_CARD, $body->jsonSerialize(), (array)$e->getHttpResponse());
            throw $e;
        }
        if ($response->isSuccess()) {
            $response = $response->getResult()->getCard();
            $this->writeSuccessLog(SquareLog::ACTION_CREATE_CUSTOMER_CARD, $body->jsonSerialize(), $response->jsonSerialize());
            
            return $response;
        }
        $this->writeErrorLog(SquareLog::ACTION_CREATE_CUSTOMER_CARD, $body->jsonSerialize(), $response->getErrors());
        throw new SquareException($response->getErrors());
    }
}
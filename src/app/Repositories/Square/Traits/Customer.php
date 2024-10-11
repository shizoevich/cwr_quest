<?php

namespace App\Repositories\Square\Traits;

use App\Exceptions\Square\SquareException;
use App\Models\Square\SquareLog;
use Log;
use Square\Exceptions\ApiException;
use Square\Models\Address;
use Square\Models\Country;
use Square\Models\CreateCustomerRequest;
use Square\Models\UpdateCustomerRequest;
use Uuid;

/**
 * Trait Customer
 * @package App\Repositories\Square\Traits
 */
trait Customer
{
    /**
     * @inheritDoc
     */
    public function getCustomer(string $customerId)
    {
        $client = $this->getClient();
        
        return optional($client->getCustomersApi()->retrieveCustomer($customerId)->getResult())->getCustomer();
    }
    
    /**
     * @inheritDoc
     */
    public function createCustomer(array $patientInfo, string $idempotencyKey = null, int $retryCount = 1)
    {
        if (!$idempotencyKey) {
            $idempotencyKey = Uuid::generate(4)->string;
        }
        $body = new CreateCustomerRequest();
        $body->setIdempotencyKey($idempotencyKey);
        if (isset($patientInfo['first_name'])) {
            $body->setGivenName($patientInfo['first_name']);
        }
        if (isset($patientInfo['last_name'])) {
            $body->setFamilyName($patientInfo['last_name']);
        }
        if (isset($patientInfo['email'])) {
            $body->setEmailAddress($patientInfo['email']);
        }
        if (isset($patientInfo['mobile_phone'])) {
            $body->setPhoneNumber($patientInfo['mobile_phone']);
        }
        
        $address = new Address();
        if (isset($patientInfo['address_line_1'])) {
            $address->setAddressLine1($patientInfo['address_line_1']);
        }
        if (isset($patientInfo['city'])) {
            $address->setLocality($patientInfo['city']);
        }
        if (isset($patientInfo['state'])) {
            $address->setAdministrativeDistrictLevel1($patientInfo['state']);
        }
        if (isset($patientInfo['zip'])) {
            $address->setPostalCode($patientInfo['zip']);
        }
        $address->setCountry(Country::US);
        $body->setAddress($address);
        
        $client = $this->getClient();
        try {
            $response = $client->getCustomersApi()->createCustomer($body);
        } catch (ApiException $e) {
            if ($retryCount > 0) {
                Log::error($e->getMessage());
                
                return $this->createCustomer($patientInfo, $idempotencyKey, --$retryCount);
            }
            $this->writeErrorLog(SquareLog::ACTION_CREATE_CUSTOMER, $body->jsonSerialize(), (array)$e->getHttpResponse());
            throw $e;
        }
        if ($response->isSuccess()) {
            $response = $response->getResult()->getCustomer();
            $this->writeSuccessLog(SquareLog::ACTION_CREATE_CUSTOMER, $body->jsonSerialize(), $response->jsonSerialize());
            
            return $response;
        }
        $this->writeErrorLog(SquareLog::ACTION_CREATE_CUSTOMER, $body->jsonSerialize(), $response->getErrors());
        throw new SquareException($response->getErrors());
    }
    
    /**
     * @inheritDoc
     */
    public function updateCustomer(string $customerId, array $patientInfo, int $retryCount = 1)
    {
        $body = new UpdateCustomerRequest();
        if (isset($patientInfo['email'])) {
            $body->setEmailAddress($patientInfo['email']);
        }
        
        $client = $this->getClient();
        try {
            $response = $client->getCustomersApi()->updateCustomer($customerId, $body);
        } catch (ApiException $e) {
            if ($retryCount > 0) {
                Log::error($e->getMessage());
                
                return $this->updateCustomer($customerId, $patientInfo, --$retryCount);
            }
            $this->writeErrorLog(SquareLog::ACTION_UPDATE_CUSTOMER, $body->jsonSerialize(), (array)$e->getHttpResponse());
            throw $e;
        }
        if ($response->isSuccess()) {
            $response = $response->getResult()->getCustomer();
            $this->writeSuccessLog(SquareLog::ACTION_UPDATE_CUSTOMER, $body->jsonSerialize(), $response->jsonSerialize());
            
            return $response;
        }
        $this->writeErrorLog(SquareLog::ACTION_UPDATE_CUSTOMER, $body->jsonSerialize(), $response->getErrors());
        throw new SquareException($response->getErrors());
    }
}
<?php

namespace App\Components\Square;

use App\PatientSquareAccount;

/**
 * Class CustomerCard
 * @package App\Components\Square
 */
class CustomerCard extends AbstractSquare
{
    /**
     * @param PatientSquareAccount $squareAccount
     * @param string  $creditCardNonce
     * @param         $zip
     * @param array   $additionalData
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     * @throws \Square\Exceptions\ApiException
     */
    public function create(PatientSquareAccount $squareAccount, string $creditCardNonce, $zip, array $additionalData = [])
    {
        $squareCustomerCard = $this->squareApi->createCustomerCard($squareAccount->external_id, $creditCardNonce, $zip, $additionalData);
        
        return $squareAccount->cards()->create([
            'card_id' => $squareCustomerCard->getId(),
            'card_brand' => $squareCustomerCard->getCardBrand(),
            'last_four' => $squareCustomerCard->getLast4(),
            'exp_month' => $squareCustomerCard->getExpMonth(),
            'exp_year' => $squareCustomerCard->getExpYear(),
            'cardholder_name' => $squareCustomerCard->getCardholderName(),
            'address_line_one' => optional($squareCustomerCard->getBillingAddress())->getAddressLine1(),
            'address_line_two' => optional($squareCustomerCard->getBillingAddress())->getAddressLine2(),
            'locality' => optional($squareCustomerCard->getBillingAddress())->getLocality(),
            'administrative_district_level_one' => optional($squareCustomerCard->getBillingAddress())->getAdministrativeDistrictLevel1(),
            'postal_code' => optional($squareCustomerCard->getBillingAddress())->getPostalCode(),
            'country' => optional($squareCustomerCard->getBillingAddress())->getCountry(),
        ]);
    }
}
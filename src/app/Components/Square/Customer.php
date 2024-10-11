<?php

namespace App\Components\Square;

use App\Exceptions\Square\SquareException;
use App\Patient;
use App\PatientSquareAccount;
use Square\Exceptions\ApiException;

class Customer extends AbstractSquare
{
    
    /**
     * @param Patient $patient
     * @param array   $patientInfo
     *
     * @return PatientSquareAccount
     * @throws ApiException
     * @throws SquareException
     */
    public function createIfNotExist(Patient $patient, array $patientInfo = [])
    {
        if ($patient->squareAccount) {
            $squareCustomer = $this->squareApi->getCustomer($patient->squareAccount->external_id);
            if (!$squareCustomer) {
                return $this->create($patient, $patientInfo);
            }
            
            return $patient->squareAccount;
        }
    
        return $this->create($patient, $patientInfo);
    }
    
    /**
     * @param Patient $patient
     * @param array   $patientInfo
     *
     * @return PatientSquareAccount
     * @throws ApiException
     * @throws SquareException
     */
    public function create(Patient $patient, array $patientInfo = [])
    {
        if(!isset($patientInfo['first_name']) && !empty($patient->first_name)) {
            $patientInfo['first_name'] = $patient->first_name;
        }
        if(!isset($patientInfo['last_name']) && !empty($patient->last_name)) {
            $patientInfo['last_name'] = $patient->last_name;
        }
        if(!isset($patientInfo['email']) && !empty($patient->email)) {
            $patientInfo['email'] = $patient->email;
        }
        $squareCustomer = $this->squareApi->createCustomer($patientInfo);
        
        return $patient->squareAccount()->create([
            'patient_id' => $patient->getKey(),
            'external_id' => $squareCustomer->getId(),
            'first_name' => $squareCustomer->getGivenName(),
            'last_name' => $squareCustomer->getFamilyName(),
            'email' => $squareCustomer->getEmailAddress(),
        ]);
    }
}
<?php

namespace App\Traits\Appointments;

use App\Helpers\RetryJobQueueHelper;
use App\Patient;
use App\Option;

trait VideoSession
{
    protected function setPatientEmailIfEmpty(Patient $patient, string $email = null)
    {
        if (isset($patient->email) || !isset($email)) {
            return;
        }

        $dataForUpdate = [
            'email' => $email,
        ];

        RetryJobQueueHelper::dispatchRetryUpdatePatient(Option::OA_ACCOUNT_1, $dataForUpdate, $patient->id);
        
        $patient->update([
            'email' => $email,
        ]);
    }
}

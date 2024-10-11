<?php

namespace App\Observers;

use App\Helpers\HIPAALogger;
use App\PatientNoteDiagnoses;

class PatientNoteDiagnosesObserver
{
    public function created(PatientNoteDiagnoses $patientNoteDiagnoses)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientNoteDiagnoses),
                'event' => 'create',
                'data' => $patientNoteDiagnoses->getLogData(),
                'message' => $patientNoteDiagnoses->getCreateLogMessage(),
            ]
        );
    }

    public function deleted(PatientNoteDiagnoses $patientNoteDiagnoses)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientNoteDiagnoses),
                'event' => 'delete',
                'data' => $patientNoteDiagnoses->getLogData(),
                'message' => $patientNoteDiagnoses->getDeleteLogMessage(),
            ]
        );
    }
}

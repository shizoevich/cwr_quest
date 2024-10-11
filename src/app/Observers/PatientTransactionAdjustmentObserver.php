<?php

namespace App\Observers;

use App\Helpers\HIPAALogger;
use App\Models\Patient\PatientTransactionAdjustment;

class PatientTransactionAdjustmentObserver
{
    public function created(PatientTransactionAdjustment $patientTransactionAdjustment): void
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientTransactionAdjustment),
                'event' => 'create',
                'data' => $patientTransactionAdjustment->getLogData(),
                'message' => $patientTransactionAdjustment->getCreateLogMessage(),
            ]
        );
    }

    public function updated(PatientTransactionAdjustment $patientTransactionAdjustment): void
    {
        $dirtyFields = $patientTransactionAdjustment->getDirtyWithOriginal();
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientTransactionAdjustment),
                'event' => 'update',
                'data' => $patientTransactionAdjustment->getLogData(),
                'dirty_fields' => $dirtyFields,
                'message' => $patientTransactionAdjustment->getUpdateLogMessage($dirtyFields),
            ]
        );
    }

    public function deleted(PatientTransactionAdjustment $patientTransactionAdjustment): void
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientTransactionAdjustment),
                'event' => 'delete',
                'data' => $patientTransactionAdjustment->getLogData(),
                'message' => $patientTransactionAdjustment->getDeleteLogMessage(),
            ]
        );
    }    
}

<?php

namespace App\Observers;

use App\Helpers\HIPAALogger;
use App\Models\Patient\PatientPreprocessedTransaction;

class PatientPreprocessedTransactionObserver
{
    public function created(PatientPreprocessedTransaction $patientPreprocessedTransaction): void
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientPreprocessedTransaction),
                'event' => 'create',
                'data' => $patientPreprocessedTransaction->getLogData(),
                'message' => $patientPreprocessedTransaction->getCreateLogMessage(),
            ]
        );
    }

    public function updated(PatientPreprocessedTransaction $patientPreprocessedTransaction): void
    {
        $dirtyFields = $patientPreprocessedTransaction->getDirtyWithOriginal();
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientPreprocessedTransaction),
                'event' => 'update',
                'data' => $patientPreprocessedTransaction->getLogData(),
                'dirty_fields' => $dirtyFields,
                'message' => $patientPreprocessedTransaction->getUpdateLogMessage($dirtyFields),
            ]
        );
    }

    public function deleted(PatientPreprocessedTransaction $patientPreprocessedTransaction): void
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientPreprocessedTransaction),
                'event' => 'delete',
                'data' => $patientPreprocessedTransaction->getLogData(),
                'message' => $patientPreprocessedTransaction->getDeleteLogMessage(),
            ]
        );
    }
    
}

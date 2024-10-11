<?php

namespace App\Observers;

use App\Helpers\HIPAALogger;
use App\Models\Patient\PatientTransaction;
use App\Patient;

class PatientTransactionObserver
{
    public function created(PatientTransaction $patientTransaction): void
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientTransaction),
                'event' => 'create',
                'data' => $patientTransaction->getLogData(),
                'message' => $patientTransaction->getCreateLogMessage(),
            ]
        );
    }

    public function updated(PatientTransaction $patientTransaction): void
    {
        $dirtyFields = $patientTransaction->getDirtyWithOriginal();
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientTransaction),
                'event' => 'update',
                'data' => $patientTransaction->getLogData(),
                'dirty_fields' => $dirtyFields,
                'message' => $patientTransaction->getUpdateLogMessage($dirtyFields),
            ]
        );
    }

    public function deleted(PatientTransaction $patientTransaction): void
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientTransaction),
                'event' => 'delete',
                'data' => $patientTransaction->getLogData(),
                'message' => $patientTransaction->getDeleteLogMessage(),
            ]
        );
    }

    public function restore(PatientTransaction $patientTransaction): void
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientTransaction),
                'event' => 'restore',
                'data' => $patientTransaction->getLogData(),
                'message' => $patientTransaction->getRestoreLogMessage(),
            ]
        );
    }
}

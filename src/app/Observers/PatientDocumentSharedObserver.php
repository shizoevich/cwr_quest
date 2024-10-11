<?php

namespace App\Observers;

use App\Helpers\HIPAALogger;
use App\PatientDocumentShared;

class PatientDocumentSharedObserver
{
    public function created(PatientDocumentShared $documentShared)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($documentShared),
                'event' => 'create',
                'data' => $documentShared->getLogData(),
                'message' => $documentShared->getCreateLogMessage(),
            ]
        );
    }

    public function updated(PatientDocumentShared $documentShared)
    {
        $dirtyFields = $documentShared->getDirtyWithOriginal();
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($documentShared),
                'event' => 'update',
                'data' => $documentShared->getLogData(),
                'dirty_fields' => $dirtyFields,
                'message' => $documentShared->getUpdateLogMessage($dirtyFields),
            ]
        );
    }

    public function deleted(PatientDocumentShared $documentShared)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($documentShared),
                'event' => 'delete',
                'data' => $documentShared->getLogData(),
                'message' => $documentShared->getDeleteLogMessage(),
            ]
        );
    }

    public function restored(PatientDocumentShared $documentShared)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($documentShared),
                'event' => 'restore',
                'data' => $documentShared->getLogData(),
                'message' => $documentShared->getRestoreLogMessage(),
            ]
        );
    }
}

<?php

namespace App\Observers;

use App\Helpers\HIPAALogger;
use App\Models\Patient\DocumentRequest\PatientDocumentRequestSharedDocument;

class PatientDocumentRequestSharedDocumentObserver
{
    public function created(PatientDocumentRequestSharedDocument $document)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($document),
                'event' => 'create',
                'data' => $document->getLogData(),
                'message' => $document->getCreateLogMessage(),
            ]
        );
    }

    public function updated(PatientDocumentRequestSharedDocument $document)
    {
        $dirtyFields = $document->getDirtyWithOriginal();
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($document),
                'event' => 'update',
                'data' => $document->getLogData(),
                'dirty_fields' => $dirtyFields,
                'message' => $document->getUpdateLogMessage($dirtyFields),
            ]
        );
    }

    public function deleted(PatientDocumentRequestSharedDocument $document)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($document),
                'event' => 'delete',
                'data' => $document->getLogData(),
                'message' => $document->getDeleteLogMessage(),
            ]
        );
    }
}

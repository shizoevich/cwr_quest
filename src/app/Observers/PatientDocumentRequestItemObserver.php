<?php

namespace App\Observers;

use App\Helpers\HIPAALogger;
use App\Models\Patient\DocumentRequest\PatientDocumentRequestItem;

class PatientDocumentRequestItemObserver
{
    public function created(PatientDocumentRequestItem $item)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($item),
                'event' => 'create',
                'data' => $item->getLogData(),
                'message' => $item->getCreateLogMessage(),
            ]
        );
    }

    public function updated(PatientDocumentRequestItem $item)
    {
        $dirtyFields = $item->getDirtyWithOriginal();
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($item),
                'event' => 'update',
                'data' => $item->getLogData(),
                'dirty_fields' => $dirtyFields,
                'message' => $item->getUpdateLogMessage($dirtyFields),
            ]
        );
    }

    public function deleted(PatientDocumentRequestItem $item)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($item),
                'event' => 'delete',
                'data' => $item->getLogData(),
                'message' => $item->getDeleteLogMessage(),
            ]
        );
    }
}

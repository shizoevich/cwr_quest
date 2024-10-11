<?php

namespace App\Observers;

use App\Events\Patient\RemovalRequestListUpdated;
use App\Helpers\HIPAALogger;
use App\Models\Patient\PatientRemovalRequest;

class PatientRemovalRequestObserver
{
    public function created(PatientRemovalRequest $removalRequest)
    {
        event(new RemovalRequestListUpdated());

        HIPAALogger::logEvent(
            [
                'collection' => class_basename($removalRequest),
                'event' => 'create',
                'data' => $removalRequest->getLogData(),
                'message' => $removalRequest->getCreateLogMessage(),
            ]
        );
    }

    public function updated(PatientRemovalRequest $removalRequest)
    {
        if ($removalRequest->isDirty(['approved_at', 'status'])) {
            event(new RemovalRequestListUpdated());
        }

        $dirtyFields = $removalRequest->getDirtyWithOriginal();
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($removalRequest),
                'event' => 'update',
                'data' => $removalRequest->getLogData(),
                'dirty_fields' => $dirtyFields,
                'message' => $removalRequest->getUpdateLogMessage($dirtyFields),
            ]
        );
    }

    public function deleted(PatientRemovalRequest $removalRequest)
    {
        event(new RemovalRequestListUpdated());

        HIPAALogger::logEvent(
            [
                'collection' => class_basename($removalRequest),
                'event' => 'delete',
                'data' => $removalRequest->getLogData(),
                'message' => $removalRequest->getDeleteLogMessage(),
            ]
        );
    }
}

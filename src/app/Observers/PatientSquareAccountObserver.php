<?php

namespace App\Observers;

use App\Helpers\HIPAALogger;
use App\PatientSquareAccount;

class PatientSquareAccountObserver
{
    public function created(PatientSquareAccount $account)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($account),
                'event' => 'create',
                'data' => $account->getLogData(),
                'message' => $account->getCreateLogMessage(),
            ]
        );
    }

    public function updated(PatientSquareAccount $account)
    {
        $dirtyFields = $account->getDirtyWithOriginal();
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($account),
                'event' => 'update',
                'data' => $account->getLogData(),
                'dirty_fields' => $dirtyFields,
                'message' => $account->getUpdateLogMessage($dirtyFields),
            ]
        );
    }

    public function deleted(PatientSquareAccount $account)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($account),
                'event' => 'delete',
                'data' => $account->getLogData(),
                'message' => $account->getDeleteLogMessage(),
            ]
        );
    }
}

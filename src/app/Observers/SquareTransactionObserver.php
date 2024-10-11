<?php

namespace App\Observers;

use App\Helpers\HIPAALogger;
use App\Models\Square\SquareTransaction;

class SquareTransactionObserver
{
    public function created(SquareTransaction $transaction)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($transaction),
                'event' => 'create',
                'data' => $transaction->getLogData(),
                'message' => $transaction->getCreateLogMessage(),
            ]
        );
    }

    public function updated(SquareTransaction $transaction)
    {
        $dirtyFields = $transaction->getDirtyWithOriginal();
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($transaction),
                'event' => 'update',
                'data' => $transaction->getLogData(),
                'dirty_fields' => $dirtyFields,
                'message' => $transaction->getUpdateLogMessage($dirtyFields),
            ]
        );
    }

    public function deleted(SquareTransaction $transaction)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($transaction),
                'event' => 'delete',
                'data' => $transaction->getLogData(),
                'message' => $transaction->getDeleteLogMessage(),
            ]
        );
    }
}

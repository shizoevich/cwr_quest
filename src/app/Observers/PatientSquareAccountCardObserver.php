<?php

namespace App\Observers;

use App\Helpers\HIPAALogger;
use App\PatientSquareAccountCard;

class PatientSquareAccountCardObserver
{
    public function created(PatientSquareAccountCard $card)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($card),
                'event' => 'create',
                'data' => $card->getLogData(),
                'message' => $card->getCreateLogMessage(),
            ]
        );
    }

    public function updated(PatientSquareAccountCard $card)
    {
        $dirtyFields = $card->getDirtyWithOriginal();
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($card),
                'event' => 'update',
                'data' => $card->getLogData(),
                'dirty_fields' => $dirtyFields,
                'message' => $card->getUpdateLogMessage($dirtyFields),
            ]
        );
    }

    public function deleted(PatientSquareAccountCard $card)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($card),
                'event' => 'delete',
                'data' => $card->getLogData(),
                'message' => $card->getDeleteLogMessage(),
            ]
        );
    }
}

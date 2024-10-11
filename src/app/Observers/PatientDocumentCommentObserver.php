<?php

namespace App\Observers;

use App\Helpers\HIPAALogger;
use App\PatientDocumentComment;

class PatientDocumentCommentObserver
{
    public function created(PatientDocumentComment $comment)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($comment),
                'event' => 'create',
                'data' => $comment->getLogData(),
                'message' => $comment->getCreateLogMessage(),
            ]
        );
    }

    public function updated(PatientDocumentComment $comment)
    {
        $dirtyFields = $comment->getDirtyWithOriginal();
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($comment),
                'event' => 'update',
                'data' => $comment->getLogData(),
                'dirty_fields' => $dirtyFields,
                'message' => $comment->getUpdateLogMessage($dirtyFields),
            ]
        );
    }

    public function deleted(PatientDocumentComment $comment)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($comment),
                'event' => 'delete',
                'data' => $comment->getLogData(),
                'message' => $comment->getDeleteLogMessage(),
            ]
        );
    }

    public function restored(PatientDocumentComment $comment)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($comment),
                'event' => 'restore',
                'data' => $comment->getLogData(),
                'message' => $comment->getRestoreLogMessage(),
            ]
        );
    }
}

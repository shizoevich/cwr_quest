<?php

namespace App\Observers;

use App\Helpers\HIPAALogger;
use App\Models\Patient\PatientTemplate;

class PatientTemplateObserver
{
    public function created(PatientTemplate $template)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($template),
                'event' => 'create',
                'data' => $template->getLogData(),
                'message' => $template->getCreateLogMessage(),
            ]
        );
    }

    public function updated(PatientTemplate $template)
    {
        $dirtyFields = $template->getDirtyWithOriginal();
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($template),
                'event' => 'update',
                'data' => $template->getLogData(),
                'dirty_fields' => $dirtyFields,
                'message' => $template->getUpdateLogMessage($dirtyFields),
            ]
        );
    }

    public function deleted(PatientTemplate $template)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($template),
                'event' => 'delete',
                'data' => $template->getLogData(),
                'message' => $template->getDeleteLogMessage(),
            ]
        );
    }
}

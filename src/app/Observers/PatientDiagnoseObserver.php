<?php

namespace App\Observers;

use App\Helpers\HIPAALogger;
use App\Jobs\GoogleDrive\CopyPatientDocument;
use App\Models\Patient\PatientDiagnose;

class PatientDiagnoseObserver
{
    public function created(PatientDiagnose $patientDiagnose): void
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientDiagnose),
                'event' => 'create',
                'data' => $patientDiagnose->getLogData(),
                'message' => $patientDiagnose->getCreateLogMessage(),
            ]
        );
    }

    public function updated(PatientDiagnose $patientDiagnose): void
    {
        $dirtyFields = $patientDiagnose->getDirtyWithOriginal();
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientDiagnose),
                'event' => 'update',
                'data' => $patientDiagnose->getLogData(),
                'dirty_fields' => $dirtyFields,
                'message' => $patientDiagnose->getUpdateLogMessage($dirtyFields),
            ]
        );
    }

    public function deleted(PatientDiagnose $patientDiagnose): void
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientDiagnose),
                'event' => 'delete',
                'data' => $patientDiagnose->getLogData(),
                'message' => $patientDiagnose->getDeleteLogMessage(),
            ]
        );
    }

}

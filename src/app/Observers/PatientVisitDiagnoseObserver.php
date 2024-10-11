<?php

namespace App\Observers;

use App\Helpers\HIPAALogger;
use App\Models\Patient\Visit\PatientVisitDiagnose;

class PatientVisitDiagnoseObserver
{
    public function created(PatientVisitDiagnose $patientVisitBilling): void
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientVisitBilling),
                'event' => 'create',
                'data' => $patientVisitBilling->getLogData(),
                'message' => $patientVisitBilling->getCreateLogMessage(),
            ]
        );
    }

    public function updated(PatientVisitDiagnose $patientVisitBilling): void
    {
        $dirtyFields = $patientVisitBilling->getDirtyWithOriginal();
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientVisitBilling),
                'event' => 'update',
                'data' => $patientVisitBilling->getLogData(),
                'dirty_fields' => $dirtyFields,
                'message' => $patientVisitBilling->getUpdateLogMessage($dirtyFields),
            ]
        );
    }

    public function deleted(PatientVisitDiagnose $patientVisitBilling): void
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientVisitBilling),
                'event' => 'delete',
                'data' => $patientVisitBilling->getLogData(),
                'message' => $patientVisitBilling->getDeleteLogMessage(),
            ]
        );
    }
}

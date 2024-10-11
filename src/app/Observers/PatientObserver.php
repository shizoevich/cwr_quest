<?php

namespace App\Observers;

use App\Helpers\HIPAALogger;
use App\Models\UpcomingReauthorizationRequest;
use App\Patient;

class PatientObserver
{
    public function creating(Patient $patient)
    {
        $this->sanitizePhones($patient);
    }

    public function created(Patient $patient)
    {
        //set patient status date
        $patient->update(['status_updated_at' => $patient->created_at]);

        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patient),
                'event' => 'create',
                'data' => $patient->getLogData(),
                'message' => $patient->getCreateLogMessage(),
            ]
        );
    }

    public function updating(Patient $patient)
    {
        $this->sanitizePhones($patient);
    }

    public function updated(Patient $patient)
    {
        $dirtyFields = $patient->getDirtyWithOriginal();

        if (array_key_exists('status_id', $dirtyFields)) {
            $dispatcher = Patient::getEventDispatcher();
            Patient::unsetEventDispatcher();
            
            $patient->update(['status_updated_at' => $patient->updated_at]);
            
            Patient::setEventDispatcher($dispatcher);
        }

        if ($patient->isDirty('eff_start_date') || $patient->isDirty('visits_auth')) {
            if (!empty($patient->insurance_plan_id) && $patient->insurancePlan->is_verification_required) {
                UpcomingReauthorizationRequest::updateOrCreate(
                    ['patient_id' => $patient->id],
                    ['episode_start_date' => $patient->eff_start_date ?? now()]
                );
            }
        }

        if (count($dirtyFields)) {
            HIPAALogger::logEvent(
                [
                    'collection' => class_basename($patient),
                    'event' => 'update',
                    'data' => $patient->getLogData(),
                    'dirty_fields' => $dirtyFields,
                    'message' => $patient->getUpdateLogMessage($dirtyFields),
                ]
            );
        }
    }

    public function deleted(Patient $patient)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patient),
                'event' => 'delete',
                'data' => $patient->getLogData(),
                'message' => $patient->getDeleteLogMessage(),
            ]
        );
    }

    private function sanitizePhones(Patient $patient)
    {
        if ($patient->home_phone) {
            $patient->home_phone = sanitize_phone($patient->home_phone);
        }

        if ($patient->cell_phone) {
            $patient->cell_phone = sanitize_phone($patient->cell_phone);
        }

        if ($patient->work_phone) {
            $patient->work_phone = sanitize_phone($patient->work_phone);
        }
    }
}

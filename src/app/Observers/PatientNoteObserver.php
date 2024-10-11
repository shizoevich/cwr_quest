<?php

namespace App\Observers;

use App\Helpers\HIPAALogger;
use App\Jobs\Salary\SyncSalaryData;
use App\Models\Patient\Comment\PatientCommentMention;
use App\PatientDocumentComment;
use App\PatientNote;
use App\PatientNoteLog;
use App\Jobs\WritePatientNoteLogs;
use App\PatientVisit;

class PatientNoteObserver
{
    /**
     * Listen to the PatientNote created event.
     *
     * @param  PatientNote $patientNote
     * @return void
     */
    public function created(PatientNote $patientNote)
    {
        $user = auth()->user();
        if (isset($user)) {
            $type = $patientNote->is_finalized ? PatientNoteLog::TYPE_CREATE : PatientNoteLog::TYPE_CREATE_DRAFT;
            $logData = [
                'patient_note_id' => $patientNote->id,
                'patient_id' => $patientNote->patients_id,
                'provider_id' => $user->provider_id,
                'user_id' => $user->id,
                'type' => $type,
                'data' => json_encode($patientNote->toArray()),
            ];

            dispatch(new WritePatientNoteLogs($logData));
        }
        
        if ($patientNote->is_finalized && $patientNote->appointment_id) {
            $this->syncSalary($patientNote);
        }

        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientNote),
                'event' => 'create',
                'data' => $patientNote->getLogData(),
                'message' => $patientNote->getCreateLogMessage(),
            ]
        );
    }

    /**
     * Listen to the PatientNote updated event.
     *
     * @param  PatientNote $patientNote
     * @return void
     */
    public function updated(PatientNote $patientNote)
    {
        $user = auth()->user();
        if (isset($user)) {
            $type = $patientNote->is_finalized ? PatientNoteLog::TYPE_UPDATE : PatientNoteLog::TYPE_UPDATE_DRAFT;
            $logData = [
                'patient_note_id' => $patientNote->id,
                'patient_id' => $patientNote->patients_id,
                'provider_id' => $user->provider_id,
                'user_id' => $user->id,
                'type' => $type,
                'data' => json_encode($patientNote->getDirty()),
            ];

            dispatch(new WritePatientNoteLogs($logData));
        }
        
        if ($patientNote->isDirty(['appointment_id', 'is_finalized']) && $patientNote->is_finalized && $patientNote->appointment_id) {
            $this->syncSalary($patientNote);
        }

        $dirtyFields = $patientNote->getDirtyWithOriginal();
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientNote),
                'event' => 'update',
                'data' => $patientNote->getLogData(),
                'dirty_fields' => $dirtyFields,
                'message' => $patientNote->getUpdateLogMessage($dirtyFields),
            ]
        );
    }
    
    /**
     * @param PatientNote $patientNote
     */
    public function deleting(PatientNote $patientNote)
    {
        $commentIds = PatientDocumentComment::query()
            ->where('document_model', PatientNote::class)
            ->where('patient_documents_id', $patientNote->getKey())
            ->pluck('id');
        PatientCommentMention::query()
            ->where('model', 'PatientDocumentComment')
            ->whereIn('comment_id', $commentIds)
            ->delete();

        PatientDocumentComment::query()
            ->whereIn('id', $commentIds)
            ->each(function (PatientDocumentComment $comment) {
                $comment->delete();
            });
    }

    /**
     * Listen to the PatientNote deleted event.
     *
     * @param  PatientNote $patientNote
     * @return void
     */
    public function deleted(PatientNote $patientNote)
    {
        $user = auth()->user();
        if (isset($user)) {
            $type = $patientNote->is_finalized ? PatientNoteLog::TYPE_DELETE : PatientNoteLog::TYPE_DELETE_DRAFT;
            $logData = [
                'patient_note_id' => $patientNote->id,
                'patient_id' => $patientNote->patients_id,
                'provider_id' => $user->provider_id,
                'user_id' => $user->id,
                'type' => $type,
            ];

            dispatch(new WritePatientNoteLogs($logData));
        }
        
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientNote),
                'event' => 'delete',
                'data' => $patientNote->getLogData(),
                'message' => $patientNote->getDeleteLogMessage(),
            ]
        );
    }

    public function restored(PatientNote $patientNote)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientNote),
                'event' => 'restore',
                'data' => $patientNote->getLogData(),
                'message' => $patientNote->getRestoreLogMessage(),
            ]
        );
    }
    
    private function syncSalary(PatientNote $patientNote)
    {
        PatientVisit::query()
            ->withTrashed()
            ->where('appointment_id', $patientNote->appointment_id)
            ->each(function ($patientVisit) {
                $patientVisit->update(['needs_update_salary' => 1]);
            });

        dispatch(new SyncSalaryData());
    }
}

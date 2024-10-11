<?php

namespace App\Observers;

use App\Appointment;
use App\Helpers\HIPAALogger;
use App\Jobs\DeleteReauthorizationRequestDocument;
use App\Jobs\ProcessReauthorizationRequestDocument;
use App\Models\Patient\Comment\PatientCommentMention;
use App\PatientDocument;
use App\PatientDocumentComment;
use App\PatientDocumentType;
use App\Status;

class PatientDocumentObserver
{
    /**
     * @param PatientDocument $patientDocument
     */
    public function created(PatientDocument $patientDocument)
    {
        $assessmentIds = PatientDocumentType::getFileTypeIDsLikeInitialAssessment();

        if (in_array($patientDocument->document_type_id, $assessmentIds)) {

            // $appt = Appointment::where('patients_id', $patientDocument->patient_id)
            //     ->statusNotCancel()
            //     ->orderBy('time', 'desc') 
            //     ->first();

            // if ($appt && is_null(Appointment::where('initial_assessment_id',$patientDocument->id)->first())) {
            //     if (
            //         (!$appt->note_on_paper ||
            //             !$appt->is_initial) &&
            //         ($appt->appointment_statuses_id == Status::getCompletedId()) 
            //     ) {
            //         $appt->note_on_paper = true;
            //         $appt->is_initial = true;
            //         $appt->save();
            //     }
            // }
            
            $patientDocument->findAppointmentAndAttach();
        }

        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientDocument),
                'event' => 'create',
                'data' => $patientDocument->getLogData(),
                'message' => $patientDocument->getCreateLogMessage(),
            ]
        );
    }

    public function updated(PatientDocument $patientDocument)
    {
        $reauthorizationIds = PatientDocumentType::getFileTypeIDsLikeReauthorization();
        if (in_array($patientDocument->document_type_id, $reauthorizationIds)) {
            dispatch(new ProcessReauthorizationRequestDocument($patientDocument));
        }

        $dirtyFields = $patientDocument->getDirtyWithOriginal();
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientDocument),
                'event' => 'update',
                'data' => $patientDocument->getLogData(),
                'dirty_fields' => $dirtyFields,
                'message' => $patientDocument->getUpdateLogMessage($dirtyFields),
            ]
        );
    }

    /**
     * @param PatientDocument $patientDocument
     */
    public function deleting(PatientDocument $patientDocument)
    {
        $commentIds = PatientDocumentComment::query()
            ->where('document_model', PatientDocument::class)
            ->where('patient_documents_id', $patientDocument->getKey())
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
     * @param PatientDocument $patientDocument
     */
    public function deleted(PatientDocument $patientDocument)
    {
        $reauthorizationIds = PatientDocumentType::getFileTypeIDsLikeReauthorization();
        if (in_array($patientDocument->document_type_id, $reauthorizationIds)) {
            dispatch(new DeleteReauthorizationRequestDocument($patientDocument));
        }

        $patientDocument->detachFromAppointment();
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientDocument),
                'event' => 'delete',
                'data' => $patientDocument->getLogData(),
                'message' => $patientDocument->getDeleteLogMessage(),
            ]
        );
    }

    public function restored(PatientDocument $patientDocument)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($patientDocument),
                'event' => 'restore',
                'data' => $patientDocument->getLogData(),
                'message' => $patientDocument->getRestoreLogMessage(),
            ]
        );
    }
}

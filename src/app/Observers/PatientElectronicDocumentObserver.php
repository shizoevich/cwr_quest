<?php

namespace App\Observers;

use App\AssessmentForm;
use App\Jobs\DeleteReauthorizationRequestDocument;
use App\Jobs\Patients\AssessmentForms\Generate;
use App\Jobs\ProcessReauthorizationRequestDocument;
use App\Models\Patient\Comment\PatientCommentMention;
use App\Models\Patient\PatientElectronicDocument;
use App\PatientDocumentComment;

/**
 * Created by PhpStorm.
 * User: eremenko_aa
 * Date: 29.06.2018
 * Time: 16:24
 */
class PatientElectronicDocumentObserver
{
    /**
     * @param PatientElectronicDocument $document
     */
    public function created(PatientElectronicDocument $document)
    {
        $password = $document->type->password;
        \Bus::dispatchNow(new Generate($document, $password));

        $assessmentIds = AssessmentForm::getFileTypeIDsLikeInitialAssessment();
        if (in_array($document->document_type_id, $assessmentIds)) {
            $document->attachToAppointment();
        }

        $reauthorizationIds = AssessmentForm::getFileTypeIDsLikeReauthorization();
        if (in_array($document->document_type_id, $reauthorizationIds)) {
            dispatch(new ProcessReauthorizationRequestDocument($document));
        }
    }

    public function updated(PatientElectronicDocument $document)
    {
        $password = $document->type->password;
        \Bus::dispatchNow(new Generate($document, $password));
    }
    
    public function deleted(PatientElectronicDocument $document)
    {
        $document->detachFromAppointment();
    }
    
    public function deleting(PatientElectronicDocument $document)
    {
        $this->deleteDocumentComments($document);
    }

    private function deleteDocumentComments(PatientElectronicDocument $document)
    {
        $reauthorizationIds = AssessmentForm::getFileTypeIDsLikeReauthorization();
        if (in_array($document->document_type_id, $reauthorizationIds)) {
            dispatch(new DeleteReauthorizationRequestDocument($document));
        }

        $commentIds = PatientDocumentComment::query()
            ->where('document_model', PatientElectronicDocument::class)
            ->where('patient_documents_id', $document->getKey())
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
}
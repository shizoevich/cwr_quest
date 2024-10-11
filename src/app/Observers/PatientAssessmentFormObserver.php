<?php

namespace App\Observers;

use App\Models\Patient\Comment\PatientCommentMention;
use App\PatientAssessmentForm;
use App\PatientDocumentComment;

class PatientAssessmentFormObserver
{
    /**
     * @param PatientAssessmentForm $assessmentForm
     */
    public function deleting(PatientAssessmentForm $assessmentForm)
    {
        $commentIds = PatientDocumentComment::query()
            ->where('document_model', PatientAssessmentForm::class)
            ->where('patient_documents_id', $assessmentForm->getKey())
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

<?php

namespace App\Console\Commands\SingleUse;

use App\Appointment;
use App\AssessmentForm;
use App\Models\Billing\BillingPeriod;
use App\Models\Patient\Comment\PatientCommentMention;
use App\Models\Patient\PatientElectronicDocument;
use App\PatientAssessmentForm;
use App\PatientDocument;
use App\PatientDocumentComment;
use App\PatientDocumentType;
use App\PatientDocumentUploadInfo;
use App\PatientNote;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Query\JoinClause;

class DeleteComments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comments:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        PatientDocumentComment::query()
            ->where('document_model', 'App\\PatientElectronicDocument')
            ->each(function (PatientDocumentComment $comment) {
                $comment->update(['document_model' => PatientElectronicDocument::class]);
            });
        $this->deletePatientDocumentComments();
        $this->deleteAssessmentDocumentComments();
        $this->deletePnDocumentComments();
        $this->deleteElectronicDocumentComments();
        $this->deleteMentions();
    }

    private function deleteComments($commentIds)
    {
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
    
    private function deleteMentions()
    {
        $ids = PatientCommentMention::query()
            ->leftJoin('patient_document_comments', 'patient_document_comments.id', '=', 'provider_comment_mentions.comment_id')
            ->where('provider_comment_mentions.model', 'PatientDocumentComment')
            ->whereNull('patient_document_comments.id')
            ->pluck('provider_comment_mentions.comment_id');

        PatientCommentMention::query()->whereIn('comment_id', $ids)->where('model', 'PatientDocumentComment')->delete();
        $ids = PatientCommentMention::query()
            ->leftJoin('patient_comments', 'patient_comments.id', '=', 'provider_comment_mentions.comment_id')
            ->where('provider_comment_mentions.model', 'PatientComment')
            ->whereNull('patient_comments.id')
            ->pluck('provider_comment_mentions.comment_id');
        PatientCommentMention::query()->whereIn('comment_id', $ids)->where('model', 'PatientComment')->delete();
    }
    
    private function deletePatientDocumentComments()
    {
        $commentIds = PatientDocumentComment::query()
            ->leftJoin('patient_documents', 'patient_document_comments.patient_documents_id', '=', 'patient_documents.id')
            ->where(function($query) {
                $query->whereNotNull('patient_documents.deleted_at')
                    ->orWhereNull('patient_documents.id');
            })
            ->where('patient_document_comments.document_model', PatientDocument::class)
            ->pluck('patient_document_comments.id');
        $this->deleteComments($commentIds);
    }
    
    private function deleteAssessmentDocumentComments()
    {
        $commentIds = PatientDocumentComment::query()
            ->leftJoin('patients_assessment_forms', 'patient_document_comments.patient_documents_id', '=', 'patients_assessment_forms.id')
            ->where(function($query) {
                $query->whereNotNull('patients_assessment_forms.deleted_at')
                    ->orWhereNull('patients_assessment_forms.id');
            })
            ->where('patient_document_comments.document_model', PatientAssessmentForm::class)
            ->pluck('patient_document_comments.id');
        $this->deleteComments($commentIds);
    }
    
    private function deletePnDocumentComments()
    {
        $commentIds = PatientDocumentComment::query()
            ->leftJoin('patient_notes', 'patient_document_comments.patient_documents_id', '=', 'patient_notes.id')
            ->where(function($query) {
                $query->whereNotNull('patient_notes.deleted_at')
                    ->orWhereNull('patient_notes.id');
            })
            ->where('patient_document_comments.document_model', PatientNote::class)
            ->pluck('patient_document_comments.id');
        $this->deleteComments($commentIds);
    }
    
    private function deleteElectronicDocumentComments()
    {
        $commentIds = PatientDocumentComment::query()
            ->leftJoin('patient_electronic_documents', 'patient_document_comments.patient_documents_id', '=', 'patient_electronic_documents.id')
            ->where(function($query) {
                $query->whereNotNull('patient_electronic_documents.deleted_at')
                    ->orWhereNull('patient_electronic_documents.id');
            })
            ->where('patient_document_comments.document_model', PatientElectronicDocument::class)
            ->pluck('patient_document_comments.id');
        $this->deleteComments($commentIds);
    }
}

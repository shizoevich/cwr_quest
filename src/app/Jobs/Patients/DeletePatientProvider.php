<?php

namespace App\Jobs\Patients;

use App\Events\NeedsWriteSystemComment;
use App\Models\PatientHasProvider;
use App\Patient;
use App\PatientDocumentComment;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DeletePatientProvider implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $patient;
    private $exceptProviderIds;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Patient $patient, array $exceptProviderIds = [])
    {
        $this->patient = $patient;
        $this->exceptProviderIds = $exceptProviderIds;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $patientId = $this->patient->id;
        $providers = $this->patient
            ->providers()
            ->withTrashed()
            ->select(['providers.id', 'providers.provider_name', 'users.id AS user_id'])
            ->join('users', 'users.provider_id', '=', 'providers.id')
            ->selectRaw("
                (
                    SELECT COUNT(patients_assessment_forms.id)
                    FROM patients_assessment_forms
                    WHERE patients_assessment_forms.deleted_at IS NULL AND patients_assessment_forms.creator_id = user_id
                        AND status = 1
                ) AS assessment_forms_count
            ")
            ->withCount(['progressNotes' => function($query) use ($patientId) {
                $query->where('patients_id', '=', $patientId);
            },'appointments' => function($query) use ($patientId) {
                $query->where('patients_id', '=', $patientId);
            }, 'comments' => function($query) use ($patientId) {
                $query->where('patients_id', '=', $patientId);
            }])
            ->having('progress_notes_count', 0)
            ->having('appointments_count', 0)
            ->having('comments_count', 0)
            ->when(count($this->exceptProviderIds) > 0, function($query) {
                $query->whereNotIn('providers.id', $this->exceptProviderIds);
            })->get();

        $patientDocuments = $this->patient->documents()->pluck('id');
        $patientNotes = $this->patient->patientNotes()->pluck('id');
        $patientAssessmentForms = $this->patient->assessmentForms()->pluck('id');
        foreach($providers as $provider) {

            $documentCommentsCount = PatientDocumentComment::where('document_model', 'App\PatientDocument')
                ->where('provider_id', $provider->id)
                ->whereIn('patient_documents_id', $patientDocuments)
                ->count();
            if($documentCommentsCount > 0) {
                continue;
            }
            $noteCommentsCount = PatientDocumentComment::where('document_model', 'App\PatientNote')
                ->where('provider_id', $provider->id)
                ->whereIn('patient_documents_id', $patientNotes)
                ->count();
            if($noteCommentsCount > 0) {
                continue;
            }
            $assessmentCommentsCount = PatientDocumentComment::where('document_model', 'App\PatientAssessmentForm')
                ->where('provider_id', $provider->id)
                ->whereIn('patient_documents_id', $patientAssessmentForms)
                ->count();
            if($assessmentCommentsCount == 0) {
                $patientHasProvider = PatientHasProvider::where('patients_id', $this->patient->id)
                                        ->where('providers_id', $provider->id)
                                        ->first();

                if($patientHasProvider) {
                    $patientHasProvider->delete();
                }

                $comment = trans('comments.provider_unassigned_automatically', [
                    'provider_name' => $provider->provider_name,
                ]);
                event(new NeedsWriteSystemComment($this->patient->id, $comment));
            }
        }
    }
}

<?php

namespace App\Repositories\NewPatientsCRM\PatientInquiry;

use App\Appointment;
use App\Events\NeedsWriteSystemCommentForPatientInquiry;
use App\Jobs\Comments\ParseCommentMentions;
use App\Models\Patient\DocumentRequest\PatientDocumentRequest;
use App\Models\Patient\DocumentRequest\PatientFormType;
use App\Models\Patient\Inquiry\PatientInquiry;
use App\Models\Patient\Inquiry\PatientInquirySource;
use App\Models\Patient\Inquiry\PatientInquiryStage;
use App\Models\Patient\Lead\PatientLead;
use App\Models\Patient\Lead\PatientLeadComment;
use App\Models\Patient\Lead\PatientLeadDiagnose;
use App\Models\Patient\PatientElectronicDocument;
use App\Models\Patient\PatientTag;
use App\Patient;
use App\PatientComment;
use App\PatientDocument;
use App\PatientDocumentType;
use App\PatientDocumentUploadInfo;
use App\PatientInsurancePlan;
use App\PatientLeadDocument;
use App\PatientLeadDocumentUploadInfo;
use App\PatientStatus;
use App\Repositories\NewPatientsCRM\PatientLead\PatientLeadRepositoryInterface;
use App\Repositories\Patient\PatientRepositoryInterface;
use App\Status;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\Patient\DocumentRequest\PatientDocumentRequest as DocumentService;
use Illuminate\Support\Facades\Auth;
use Twilio\Exceptions\RestException;

class PatientInquiryRepository implements PatientInquiryRepositoryInterface
{
    private const REQUIRED_FORMS = [
        'new_patient',
        'payment_for_service',
        'agreement_for_service_and_hipaa_privacy_notice_and_patient_rights',
    ];

    private const COMMENTS_PER_PAGE = 20;

    private $patientRepository;
    private $patientLeadRepository;

    public function __construct(
        PatientRepositoryInterface     $patientRepository,
        PatientLeadRepositoryInterface $patientLeadRepository
    )
    {
        $this->patientRepository = $patientRepository;
        $this->patientLeadRepository = $patientLeadRepository;
    }

    public function create(array $data, ?int $stageId = null, ?bool $isInquirableReturning = null, ?bool $forbidInquirableUpdate = false): PatientInquiry
    {
        $stageId = $stageId ?? PatientInquiryStage::getInboxId();

        $inquirable = $this->createOrUpdateInquirable($data, $stageId, $forbidInquirableUpdate);
        $isReturning = $this->getInquirableIsReturning($data, $isInquirableReturning);

        $inquiry = PatientInquiry::create([
            'inquirable_id' => $inquirable->id,
            'inquirable_type' => get_class($inquirable),
            'stage_id' => $stageId,
            'registration_method_id' => $data['registration_method_id'] ?? null,
            'source_id' => $data['source_id'],
            'marketing_activity' => $data['marketing_activity'] ?? null,
            'from_bdr' => $data['from_bdr'] ?? null,
            'admin_id' => auth()->id() ?? null,
            'is_returning' => $isReturning,
            'stage_changed_at' => Carbon::now(),
        ]);

        $this->attachPatientTagReturningIfNeeded($inquiry, $inquirable, $isReturning);

        $this->loadPatientInquiryRelationsData($inquiry);

        $comment = auth()->check()
            ? trans('comments.patient_inquiry_was_created_by_admin', [
                'admin_name' => auth()->user()->getFullname(),
            ])
            : trans('comments.patient_inquiry_was_created_by_system');

        event(new NeedsWriteSystemCommentForPatientInquiry(
            $inquiry->id,
            $comment,
            auth()->id() ?? null
        ));

        return $inquiry;
    }

    public function update(array $data)
    {
        if ($data['model_classname'] === class_basename(Patient::class)) {
            $patient = Patient::find($data['id']);
            return $this->patientRepository->update($data, $patient);
        }

        if ($data['model_classname'] === class_basename(PatientLead::class)) {
            $patientLead = PatientLead::find($data['id']);
            return $this->patientLeadRepository->update($data, $patientLead);
        }
    }

    public function getInquiries(array $params, bool $archived = false): array
    {
        $stageId = isset($params['stage_id']) ? $params['stage_id'] : null;

        $query = PatientInquiry::query()
            ->select([
                '*',
                DB::raw('IF(inquirable_type = "App\\\Patient", 1, 0) as is_patient_created'), // mysql need two backslashes, so there is no option using Patient::class
            ])
            ->with([
                'source',
                'source.channel',
                'inquirable',
                'inquirable.insurance',
                'inquirable.therapyType',
                'registrationMethod',
            ]);

        if ($archived) {
            $query->archived();
        } else {
            $query->active();
        }

        if (isset($stageId)) {
            $query->where('stage_id', $stageId);
        }

        if (isset($params['source_id'])) {
            $query->whereIn('source_id', $params['source_id']);
        }

        if (isset($params['search_text'])) {
            $searchText = $params['search_text'];

            $query->where(function ($subQuery) use ($searchText) {
                $subQuery->whereHas('patientLead', function ($query) use ($searchText) {
                    $query->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%$searchText%");
                });

                $subQuery->orWhereHas('patient', function ($query) use ($searchText) {
                    $query->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%$searchText%");
                });
            });
        }

        $inquiries = $query->orderBy('stage_changed_at', 'desc')
            ->paginate(15, ['*'], 'page', $params['page']);

        $this->processInquiries($inquiries, $stageId);

        return [
            'data' => $inquiries->items(),
            'meta' => [
                'current_page' => $inquiries->currentPage(),
                'total' => $inquiries->total(),
                'last_page' => $inquiries->lastPage(),
            ],
        ];
    }

    private function processInquiries(LengthAwarePaginator &$inquiries, $stageId): void
    {
        $inquiries->transform(function ($inquiry) {
            if ($inquiry->isPatientCreated()) {
                $appointments = $inquiry->inquirable->appointments()
                    ->where('time', '>', $inquiry->created_at->timestamp)
                    ->orderBy('time', 'desc')
                    ->get();

                $inquiry->has_cancelled_appointment = $appointments->whereIn('appointment_statuses_id', Status::getOtherCancelStatusesId())->isNotEmpty();

                $inquiry->onboarding_complete_comment = $inquiry->inquirable->comments()
                    ->with('admin')
                    ->where('comment_type', PatientComment::ONBOARDING_COMPLETE_TYPE)
                    ->latest()
                    ->first();

                $inquiry->initial_appointment_is_completed = $appointments->whereIn('appointment_statuses_id', Status::getCompletedVisitCreatedStatusesId())->isNotEmpty();

                $this->loadPatientSurveyInfo($inquiry);

                $inquiry->load([
                    'inquirable.therapyType',
                    'inquirable.alert',
                    'inquirable.appointments' => function ($query) use ($inquiry) {
                        $query->where('time', '>', $inquiry->created_at->timestamp)
                            ->orderBy('time', 'asc');
                    },
                    'inquirable.appointments.provider'
                ]);

                return $this->processInquiriesAppointments($inquiry);
            }

            return $inquiry;
        });

        // @todo: when migrating to Laravel 5.8 and above use "morphWith()" in query to load polymorphic nested relation
        $this->loadPatientStatusForInquiries($inquiries->getCollection());
        $this->loadPatientProvidersForInquiries($inquiries->getCollection());

        if (!in_array($stageId, PatientInquiryStage::getStageIdsWithoutFormRequest())) {
            $this->loadLastDocumentAndDocumentRequestForInquiries($inquiries->getCollection());
        }

        if ($stageId === PatientInquiryStage::getAppointmentScheduledId()) {
            $inquiries = $inquiries->sortBy(function ($inquiry) {
                return optional($inquiry->next_appointment)->time;
            })->values();
        }
    }

    public function changeStage(PatientInquiry $inquiry, array $data, ?string $reason = null): PatientInquiry
    {
        if (isset($data['forms'])) {
            $forms = ['forms' => $data['forms']];

            if (isset($data['send_via_email']) && $data['send_via_email']) {
                $forms['email'] = $data['email'];
            }
            if (isset($data['send_via_sms']) && $data['send_via_sms']) {
                $forms['phone'] = $data['phone'];
            }

            $documentService = new DocumentService();
            $documentRequest = $documentService->save($inquiry->inquirable, $forms);

            $documentService->sendViaEmail($documentRequest);
            try {
                $documentService->sendViaSms($documentRequest);
            } catch (RestException $e) {
                \App\Helpers\SentryLogger::captureException($e);
            }
        }

        $updatedData = [
            'stage_id' => (int)$data['stage_id'],
            'stage_changed_at' => Carbon::now(),
        ];

        if (isset($data['onboarding_data'])) {
            $updatedData['onboarding_date'] = $data['onboarding_data']['date'];
            $updatedData['onboarding_time'] = $data['onboarding_data']['time'];
            $updatedData['onboarding_phone'] = $data['onboarding_data']['phone'];
        }

        $oldStageId = $inquiry->stage_id;
        $inquiry->update($updatedData);

        $this->loadPatientInquiryRelationsData($inquiry);

        if (is_null($reason) && auth()->check()) {
            $comment = trans('comments.patient_inquiry_stage_changed_by_admin', [
                'old_stage_name' => PatientInquiryStage::find($oldStageId)->name,
                'new_stage_name' => PatientInquiryStage::find($data['stage_id'])->name,
                'admin_name' => auth()->user()->getFullname(),
                'comment' => isset($data['comment']) ? 'with the following comment: ' . $data['comment'] : '',
            ]);
        } else {
            if ($reason === PatientInquiry::REASON_FOR_STAGE_CHANGE_COMPLETED_APPOINTMENT) {
                $commentKey = 'comments.patient_inquiry_stage_changed_by_system_due_to_complete';
            } else if ($reason === PatientInquiry::REASON_FOR_STAGE_CHANGE_CANCELED_APPOINTMENT) {
                $commentKey = 'comments.patient_inquiry_stage_changed_by_system_due_to_cancel';
            } else {
                $commentKey = 'comments.patient_inquiry_stage_changed_by_system';
            }

            $comment = trans($commentKey, [
                'old_stage_name' => PatientInquiryStage::find($oldStageId)->name,
                'new_stage_name' => PatientInquiryStage::find($data['stage_id'])->name,
            ]);
        }

        event(new NeedsWriteSystemCommentForPatientInquiry(
            $inquiry->id,
            $comment,
            auth()->id()
        ));

        return $inquiry;
    }

    public function createPatientFromPatientLead(PatientInquiry $inquiry): PatientInquiry
    {
        if ($inquiry->isPatientCreated()) {
            $this->loadPatientInquiryRelationsData($inquiry);

            return $inquiry;
        }

        $patientLead = $inquiry->inquirable;

        $patientLead->load(['diagnoses:diagnose_id', 'templates', 'comments', 'comments.mentions', 'faxes']);

        $patientData = [
            'first_name' => $patientLead->first_name,
            'last_name' => $patientLead->last_name,
            'middle_initial' => $patientLead->middle_initial,
            'sex' => $patientLead->sex,
            'preferred_language_id' => $patientLead->preferred_language_id,
            'email' => $patientLead->email,
            'date_of_birth' => $patientLead->date_of_birth,
            'secondary_email' => $patientLead->secondary_email,
            'cell_phone' => format_phone($patientLead->cell_phone),
            'home_phone' => format_phone($patientLead->home_phone),
            'work_phone' => format_phone($patientLead->work_phone),
            'preferred_phone' => $patientLead->preferred_phone,
            'address' => $patientLead->address,
            'address_2' => $patientLead->address_2,
            'city' => $patientLead->city,
            'state' => $patientLead->state,
            'zip' => $patientLead->zip,
            'provider_id' => $patientLead->provider_id,
            'insurance_id' => $patientLead->primary_insurance_id,
            'subscriber_id' => $patientLead->subscriber_id,
            'plan_name' => isset($patientLead->insurance_plan_id) ? PatientInsurancePlan::find($patientLead->insurance_plan_id)->name : null,
            'is_self_pay' => $patientLead->is_self_pay,
            'self_pay' => $patientLead->self_pay,
            'visit_copay' => $patientLead->visit_copay,
            'deductible' => $patientLead->deductible,
            'deductible_met' => $patientLead->deductible_met,
            'deductible_remaining' => $patientLead->deductible_remaining,
            'insurance_pay' => $patientLead->insurance_pay,
            'therapy_type_id' => $patientLead->therapy_type_id,
            'auth_number' => $patientLead->auth_number,
            'visits_auth' => $patientLead->visits_auth,
            'visits_auth_left' => $patientLead->visits_auth_left,
            'eff_start_date' => $patientLead->eff_start_date,
            'eff_stop_date' => $patientLead->eff_stop_date,
            'eligibility_payer_id' => $patientLead->eligibility_payer_id,
            'is_payment_forbidden' => $patientLead->is_payment_forbidden,
        ];

        if ($patientLead->has('diagnoses')) {
            foreach ($patientLead->diagnoses()->pluck('diagnose_id') as $diagnoseId) {
                $patientData['diagnoses'][] = [
                    'id' => $diagnoseId,
                ];
            }
        }
        if ($patientLead->has('templates')) {
            foreach ($patientLead->templates()->get() as $template) {
                $patientData['templates'][] = [
                    'pos' => $template->pos,
                    'patient_insurances_procedure_id' => $template->patient_insurances_procedure_id,
                    'cpt' => $template->cpt,
                    'modifier_a' => $template->modifier_a,
                    'modifier_b' => $template->modifier_b,
                    'modifier_c' => $template->modifier_c,
                    'modifier_d' => $template->modifier_d,
                    'diagnose_pointer' => $template->diagnose_pointer,
                    'charge' => $template->charge,
                    'days_or_units' => $template->days_or_units,
                ];
            }
        }
        if ($patientLead->has('faxes')) {
            foreach ($patientLead->faxes()->get() as $fax) {
                $patientData['faxes'][] = $fax->id;
            }
        }
        $documents = PatientLeadDocument::withoutAdminScope()
            ->where('patient_lead_id', $patientLead->id)
            ->get();
        foreach ($documents as $document) {
            $patientData['documents'][] = [
                'id' => $document->id,
                'original_document_name' => $document->original_document_name,
                'aws_document_name' => $document->aws_document_name,
                'is_tridiuum_document' => $document->is_tridiuum_document,
                'google_drive' => $document->google_drive,
                'document_type_id' => $document->document_type_id,
                'visible' => $document->visible,
                'only_for_admin' => $document->only_for_admin,
            ];
        }

        $patient = Patient::query()
            ->where([
                'first_name' => $patientLead->first_name,
                'last_name'  => $patientLead->last_name,
                'date_of_birth' => $patientLead->date_of_birth,
            ])
            ->first();
        $needToCreatePatientComment = false;

        if (empty($patient)) {
            $patient = $this->patientRepository->create($patientData);
            $needToCreatePatientComment = true;
        }

        $patientLead->update(['patient_id' => $patient->id]);
        $inquiry->update([
            'inquirable_id' => $patient->id,
            'inquirable_type' => get_class($patient),
        ]);

        if (!empty($patientLead->comments)) {
            foreach ($patientLead->comments as $patientLeadComment) {
                $patientComment = PatientComment::create([
                    'patient_id' => $patient->id,
                    'admin_id' => $patientLeadComment->admin_id,
                    'comment' => $patientLeadComment->comment,
                    'is_system_comment' => $patientLeadComment->is_system_comment,
                    'created_at' => $patientLeadComment->created_at,
                    'updated_at' => $patientLeadComment->updated_at,
                ]);

                if (!empty($patientLeadComment->mentions)) {
                    $patientLeadComment->mentions()->update([
                        'comment_id' => $patientComment->id,
                        'model' => 'PatientComment',
                    ]);
                }
            }
        }

        $patientLead->templates()->delete();
        foreach ($documents as $document) {
            $document->uploadInfo()->delete();
            $document->delete();
        }
        PatientLeadDiagnose::where('patient_lead_id', $patientLead->id)->delete();
        $patientLead->comments()->delete();
        $patientLead->delete();

        if ($needToCreatePatientComment) {
            $comment = auth()->check()
                ? trans('comments.patient_inquiry_patient_was_created_from_patient_lead_by_admin', [
                    'admin_name' => auth()->user()->getFullname(),
                ])
                : trans('comments.patient_inquiry_patient_was_created_from_patient_lead_by_system');

            event(new NeedsWriteSystemCommentForPatientInquiry(
                $inquiry->id,
                $comment,
                auth()->id()
            ));
        }

        $this->loadPatientInquiryRelationsData($inquiry);

        return $inquiry;
    }

    public function archive(PatientInquiry $inquiry, array $data): PatientInquiry
    {
        $inquiry->update([
            'is_archived' => true,
            'closed_at' => now(),
        ]);

        $this->loadPatientInquiryRelationsData($inquiry);
        $this->detachPatientTagReturningIfNeeded($inquiry);

        event(new NeedsWriteSystemCommentForPatientInquiry(
            $inquiry->id,
            trans('comments.patient_inquiry_was_archived', [
                'admin_name' => auth()->user()->getFullname(),
                'comment' => $data['comment'],
            ]),
            auth()->id()
        ));

        return $inquiry;
    }

    public function close(PatientInquiry $inquiry): PatientInquiry
    {
        $inquiry->update([
            'closed_at' => now(),
        ]);

        $this->loadPatientInquiryRelationsData($inquiry);
        $this->detachPatientTagReturningIfNeeded($inquiry);

        event(new NeedsWriteSystemCommentForPatientInquiry($inquiry->id, trans('comments.patient_inquiry_was_closed')));

        return $inquiry;
    }

    public function getComments(PatientInquiry $inquiry): LengthAwarePaginator
    {
        $selects = [];

        if ($inquiry->isPatientCreated()) {
            $selects[] = "
            SELECT 'PatientDocument' AS model, `patient_documents`.`id`, `patient_documents`.`created_at`, NULL AS provider_name, NULL AS provider_name_from_appointment, NULL AS diagnosis_icd_code, NULL AS date_of_service, NULL AS long_range_treatment_goal, NULL AS shortterm_behavioral_objective, NULL AS additional_comments, NULL AS plan,
              NULL AS interventions, NULL AS progress_and_outcome, `patient_documents`.`aws_document_name`, `patient_documents`.`original_document_name`, NULL AS `comment`, NULL AS `comment_type`, NULL AS `appointment_id`, NULL AS `comment_metadata`, CONCAT(uploader_users_meta.firstname, ' ', uploader_users_meta.lastname) AS `full_admin_name`,
              NULL AS `assessment_doc`, `patient_documents`.`other_document_type`, `patient_document_types`.`type` AS `document_type`, NULL AS `is_finalized`,
              NULL AS `is_system_comment`, NULL AS `start_editing_date`, patient_documents.only_for_admin, 1 AS `is_commentable`, `patient_documents`.`is_tridiuum_document`, NULL as `metadata`
            FROM `patient_documents`
            JOIN `patient_document_types` ON `patient_documents`.`document_type_id` = `patient_document_types`.`id`
            JOIN `patient_document_upload_info` ON `patient_documents`.`id` = `patient_document_upload_info`.`patient_document_id`
            JOIN `users_meta` AS `uploader_users_meta` ON `patient_document_upload_info`.`user_id` = `uploader_users_meta`.`user_id`
            WHERE `patient_documents`.`patient_id` = $inquiry->inquirable_id AND `patient_documents`.`visible` = 1 AND `patient_documents`.`deleted_at` IS NULL";

            $selects[] = "
            SELECT IF(`is_system_comment` = 0, 'PatientComment','PatientAlert') AS model, `patient_comments`.`id`, `patient_comments`.`created_at`, `provider_direct`.`provider_name` as `provider_name`, `provider_from_appointment`.`provider_name` AS provider_name_from_appointment,
                NULL AS diagnosis_icd_code, NULL AS date_of_service, NULL AS long_range_treatment_goal, NULL AS shortterm_behavioral_objective, NULL AS additional_comments, NULL AS plan, NULL AS interventions, NULL AS progress_and_outcome, NULL AS aws_document_name, NULL AS original_document_name, IF(`default_comment_id` is null, `patient_comments`.`comment`, patient_default_comments.comment) AS comment,
                `patient_comments`.`comment_type` AS `comment_type`, `patient_comments`.`appointment_id` AS `appointment_id`, `patient_comments`.`metadata` AS `comment_metadata`, CONCAT(`users_meta`.`firstname`, ' ', `users_meta`.`lastname`) AS `full_admin_name`, NULL AS `assessment_doc`, NULL AS `other_document_type`,
                NULL AS `document_type`, NULL AS `is_finalized`, `is_system_comment`, NULL AS `start_editing_date`, 0 AS only_for_admin, 0 AS `is_commentable`, NULL AS `is_tridiuum_document`, `patient_comments`.`metadata`
            FROM `patient_comments`
            LEFT JOIN `appointments` ON `appointments`.`id` = `patient_comments`.`appointment_id`
            LEFT JOIN `providers` AS provider_from_appointment ON provider_from_appointment.`id` = `appointments`.`providers_id`
            LEFT JOIN `providers` AS provider_direct ON provider_direct.`id` = `patient_comments`.`provider_id`
            LEFT JOIN `users_meta` ON `users_meta`.`user_id` = `patient_comments`.`admin_id`
            LEFT JOIN `patient_default_comments` ON `patient_default_comments`.`id` = `patient_comments`.`default_comment_id`
            WHERE `patient_id` = $inquiry->inquirable_id AND patient_comments.deleted_at IS NULL" .
            (!Auth::user()->isAdmin() ? " AND `patient_comments`.`only_for_admin` != 1" : "");        
        } else {
            $selects[] = "
            SELECT 'PatientLeadDocument' AS model, `patient_lead_documents`.`id`, `patient_lead_documents`.`created_at`, NULL AS provider_name, NULL AS provider_name_from_appointment, NULL AS diagnosis_icd_code, NULL AS date_of_service, NULL AS long_range_treatment_goal, NULL AS shortterm_behavioral_objective, NULL AS additional_comments, NULL AS plan,
              NULL AS interventions, NULL AS progress_and_outcome, `patient_lead_documents`.`aws_document_name`, `patient_lead_documents`.`original_document_name`, NULL AS `comment`, NULL AS `comment_type`, NULL AS `appointment_id`, NULL AS `comment_metadata`, CONCAT(uploader_users_meta.firstname, ' ', uploader_users_meta.lastname) AS `full_admin_name`,
              NULL AS `assessment_doc`, `patient_lead_documents`.`other_document_type`, `patient_document_types`.`type` AS `document_type`, NULL AS `is_finalized`,
              NULL AS `is_system_comment`, NULL AS `start_editing_date`, patient_lead_documents.only_for_admin, 1 AS `is_commentable`, `patient_lead_documents`.`is_tridiuum_document`, NULL as `metadata`
            FROM `patient_lead_documents`
            JOIN `patient_document_types` ON `patient_lead_documents`.`document_type_id` = `patient_document_types`.`id`
            JOIN `patient_lead_document_upload_info` ON `patient_lead_documents`.`id` = `patient_lead_document_upload_info`.`patient_lead_document_id`
            JOIN `users_meta` AS `uploader_users_meta` ON `patient_lead_document_upload_info`.`user_id` = `uploader_users_meta`.`user_id`
            WHERE `patient_lead_documents`.`patient_lead_id` = $inquiry->inquirable_id AND `patient_lead_documents`.`visible` = 1 AND `patient_lead_documents`.`deleted_at` IS NULL";

            $selects[] = "
            SELECT IF(`is_system_comment` = 0, 'PatientComment','PatientAlert') AS model, `patient_lead_comments`.`id`, `patient_lead_comments`.`created_at`, NULL AS provider_name, NULL AS provider_name_from_appointment,
              NULL AS diagnosis_icd_code, NULL AS date_of_service, NULL AS long_range_treatment_goal, NULL AS shortterm_behavioral_objective, NULL AS additional_comments, NULL AS plan, NULL AS interventions, NULL AS progress_and_outcome, NULL AS aws_document_name, NULL AS original_document_name, `patient_lead_comments` . `comment`,
              NULL AS `comment_type`, NULL AS `appointment_id`, NULL AS `comment_metadata`, CONCAT(`users_meta`.`firstname`, ' ', `users_meta`.`lastname`) AS `full_admin_name`, NULL AS `assessment_doc`, NULL AS `other_document_type`,
              NULL AS `document_type`, NULL AS `is_finalized`, `is_system_comment`, NULL AS `start_editing_date`, 0 AS only_for_admin, 0 AS `is_commentable`, NULL AS `is_tridiuum_document`, NULL as `metadata`
            FROM `patient_lead_comments`
            LEFT JOIN `users_meta` ON `users_meta`.`user_id` = `patient_lead_comments`.`admin_id`
            WHERE `patient_lead_id` = $inquiry->inquirable_id AND patient_lead_comments.deleted_at IS NULL";
        }

        $unionedQuery = implode(' UNION ALL ', $selects);

        $data = DB::table(DB::raw("($unionedQuery) AS documents"))
            ->orderBy('documents.created_at', 'DESC')
            ->orderBy('documents.id', 'DESC')
            ->paginate(self::COMMENTS_PER_PAGE);

        foreach ($data as $note) {
            if ($note->is_commentable) {
                $note->document_uploader = $this->getPatientDocumentUploader($note->id, $note->model);
            }

            if ($note->appointment_id) {
                $appointment = Appointment::withTrashed()->find($note->appointment_id);
                $note->appointment = $appointment;
                $note->new_appointment = $note->comment_type === PatientComment::RESCHEDULE_COMMENT_TYPE && isset($appointment)
                    ? Appointment::withTrashed()->where('rescheduled_appointment_id', $appointment->id)->first()
                    : null;
            }

            if ($note->comment_metadata) {
                $note->comment_metadata = json_decode($note->comment_metadata);
            }
        }

        return $data;
    }

    public function createComment(PatientInquiry $inquiry, array $data, bool $isSystem = null)
    {
        $isSystemComment = is_null($isSystem) ? !auth()->check() : $isSystem;

        if (!$isSystemComment) {
            $data['comment'] = preg_replace("/<div><br><\/div>/", " ", $data['comment']);
            $data['comment'] = preg_replace("/<div>/", "<br>", $data['comment']);
            $data['comment'] = strip_tags($data['comment'], '<span><br>');
            $data['comment'] = preg_replace("/<br>$/", "", $data['comment']);
            $data['comment'] = trim($data['comment']);
        }

        if ($inquiry->isPatientCreated()) {
            $comment = PatientComment::create([
                'patient_id' => $inquiry->inquirable_id,
                'comment' => $data['comment'],
                'admin_id' => auth()->id() ?? null,
                'is_system_comment' => $isSystemComment,
            ]);

            if (!empty($comment)) {
                \Bus::dispatchNow(new ParseCommentMentions($data['comment'], $comment->id, 'PatientComment', $comment->patient->id));
            }

            if ($comment->provider_id) {
                $comment->load('provider');
            }
            if ($comment->admin_id) {
                $comment->load('admin');
            }
        } else {
            $comment = PatientLeadComment::create([
                'patient_lead_id' => $inquiry->inquirable_id,
                'comment' => $data['comment'],
                'admin_id' => auth()->id() ?? null,
                'is_system_comment' => $isSystemComment,
            ]);

            if (!empty($comment)) {
                \Bus::dispatchNow(new ParseCommentMentions($data['comment'], $comment->id, 'PatientLeadComment', $comment->patientLead->id));
            }
            $comment->load('admin');
        }

        return $comment;
    }

    public function createInitialSurveyComment(PatientInquiry $inquiry, array $data): PatientComment
    {
        $patient = $inquiry->inquirable;

        $needToSetInquiryStageInitialSurveyComplete = $inquiry->stage_id !== PatientInquiryStage::getInitialSurveyCompleteId() && $inquiry->stage_id !== PatientInquiryStage::getFourAppointmentsCompleteId();

        if ($needToSetInquiryStageInitialSurveyComplete) {
            app()->make(PatientInquiryRepositoryInterface::class)->changeStage(
                $inquiry,
                [
                    'stage_id' => PatientInquiryStage::getInitialSurveyCompleteId(),
                ],
                PatientInquiry::REASON_FOR_STAGE_CHANGE_COMPLETED_APPOINTMENT
            );
        }

        return $patient->comments()->create([
            'admin_id' => auth()->id() ?? null,
            'comment' => $data['comment'],
            'comment_type' => PatientComment::INITIAL_SURVEY_COMMENT_TYPE,
            'appointment_id' => $data['appointment_id'],
            'metadata' => $data['metadata'],
            'is_system_comment' => false,
            'only_for_admin' => true,
        ]);
    }

    public function createSecondSurveyComment(PatientInquiry $inquiry, array $data): PatientComment
    {
        $patient = $inquiry->inquirable;

        return $patient->comments()->create([
            'admin_id' => auth()->id() ?? null,
            'comment' => $data['comment'],
            'comment_type' => PatientComment::SECOND_SURVEY_COMMENT_TYPE,
            'appointment_id' => $data['appointment_id'],
            'metadata' => $data['metadata'],
            'is_system_comment' => false,
            'only_for_admin' => true,
        ]);
    }

    public function getCompletedInitialAppointment(PatientInquiry $inquiry): Appointment
    {
        return $inquiry->inquirable->appointments()
            ->with(['provider', 'status'])
            ->whereIn('appointment_statuses_id', Status::getCompletedVisitCreatedStatusesId())
            ->where('time', '>', $inquiry->created_at->timestamp)
            ->orderBy('time', 'asc')
            ->first();
    }

    public function createOnboardingCompleteComment(PatientInquiry $inquiry, array $data): PatientComment
    {
        $patient = $inquiry->inquirable;

        $needToSetInquiryStageOnboardingComplete = $inquiry && $inquiry->stage_id !== PatientInquiryStage::getOnboardingCompleteId();

        if ($needToSetInquiryStageOnboardingComplete) {
            app()->make(PatientInquiryRepositoryInterface::class)->changeStage(
                $inquiry,
                [
                    'stage_id' => PatientInquiryStage::getOnboardingCompleteId(),
                ],
                PatientInquiry::REASON_FOR_STAGE_CHANGE_COMPLETED_APPOINTMENT
            );
        }

        return $patient->comments()->create([
            'admin_id' => auth()->id() ?? null,
            'comment' => $data['comment'],
            'comment_type' => PatientComment::ONBOARDING_COMPLETE_TYPE,
            'metadata' => ['phone' => $data['phone']],
            'is_system_comment' => false,
            'only_for_admin' => true,
        ]);
    }

    protected function loadPatientInquiryRelationsData(PatientInquiry &$inquiry): void
    {
        $relations = ['inquirable', 'inquirable.insurance', 'inquirable.therapyType', 'registrationMethod', 'source', 'source.channel'];

        if ($inquiry->isPatientCreated()) {
            $patientRelations = [
                'inquirable.status',
                'inquirable.providers',
                'inquirable.alert',
                'inquirable.appointments' => function ($query) use ($inquiry) {
                    $query->where('time', '>', $inquiry->created_at->timestamp)
                        ->orderBy('time', 'asc');
                },
                'inquirable.appointments.provider'
            ];
            $relations = array_merge($relations, $patientRelations);
        }

        $inquiry->load($relations);

        if ($inquiry->isPatientCreated()) {
            $this->loadPatientSurveyInfo($inquiry);
            $this->processInquiriesAppointments($inquiry);

            $appointments = $inquiry->inquirable->appointments()
                ->where('time', '>', $inquiry->created_at->timestamp)
                ->orderBy('time', 'desc')
                ->get();

            $inquiry->has_cancelled_appointment = $appointments->whereIn('appointment_statuses_id', Status::getOtherCancelStatusesId())->isNotEmpty();

            $inquiry->onboarding_complete_comment = $inquiry->inquirable->comments()
                ->with('admin')
                ->where('comment_type', PatientComment::ONBOARDING_COMPLETE_TYPE)
                ->latest()
                ->first();

            $inquiry->initial_appointment_is_completed = $appointments->whereIn('appointment_statuses_id', Status::getCompletedVisitCreatedStatusesId())->isNotEmpty();

            $inquiry['inquirable']['past_appointment_provider'] = $inquiry->getPastAppointmentProvider();
        }

        $this->loadPatientInquiryLastDocument($inquiry);
    }

    protected function loadPatientSurveyInfo(PatientInquiry &$inquiry): void
    {
        $inquirable = $inquiry->inquirable;
        $inquiry->initial_survey_complete = $inquirable->comments()
            ->where('comment_type', PatientComment::INITIAL_SURVEY_COMMENT_TYPE)->exists();
        $inquiry->second_survey_complete = $inquirable->comments()
            ->where('comment_type', PatientComment::SECOND_SURVEY_COMMENT_TYPE)->exists();
    }

    protected function loadPatientInquiryLastDocument(PatientInquiry &$inquiry): void
    {
        if (in_array($inquiry->stage_id, PatientInquiryStage::getStageIdsWithoutFormRequest())) {
            return;
        }

        $lastDocument = PatientDocument::query()
            ->where([
                ['patient_id', '=', $inquiry->inquirable_id],
                ['document_type_id', '=', PatientDocumentType::getNewPatientId()],
            ])
            ->where('created_at', '>', $inquiry->created_at)
            ->orderBy('created_at', 'DESC')
            ->first();

        $inquiry->inquirable->last_document = $lastDocument;

        $requiredFormTypes = Cache::rememberForever('patient-inquiry:required-form-types', function () {
            return PatientFormType::getFormTypeIds(self::REQUIRED_FORMS);
        });

        $inquiry->inquirable->last_document_request = PatientDocumentRequest::query()
            ->where('patient_id', $inquiry->inquirable_id)
            ->with(['items'])
            ->whereHas('items', function ($query) use ($requiredFormTypes) {
                $query->whereIn('form_type_id', $requiredFormTypes);
            })
            ->when(isset($lastDocument), function($query) use ($lastDocument) {
                $query->whereHas('items', function ($query) use ($lastDocument) {
                    $query->where('id', $lastDocument->document_request_item_id);
                });
            })
            ->where('created_at', '>', $inquiry->created_at)
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    protected function createOrUpdateInquirable(array $data, int $stageId, ?bool $forbidInquirableUpdate = false)
    {
        $inquirableIsLead = isset($data['inquirable_classname'])
            ? $data['inquirable_classname'] === class_basename(PatientLead::class)
            : $stageId === PatientInquiryStage::getInboxId();

        if (isset($data['inquirable_id']) && $forbidInquirableUpdate) {
            return $inquirableIsLead
                ? PatientLead::find($data['inquirable_id'])
                : Patient::find($data['inquirable_id']);
        }

        if ($inquirableIsLead) {
            $inquirable = isset($data['inquirable_id'])
                ? $this->patientLeadRepository->update($data, PatientLead::find($data['inquirable_id']))
                : $this->patientLeadRepository->create($data);
        } else {
            $this->formatPhones($data);

            $inquirable = isset($data['inquirable_id'])
                ? $this->patientRepository->update($data, Patient::find($data['inquirable_id']))
                : $this->patientRepository->create($data);
        }

        return $inquirable;
    }

    protected function getInquirableIsReturning(array $data, ?bool $isPatientReturning = null): bool
    {
        if (!is_null($isPatientReturning)) {
            return $isPatientReturning;
        }

        return isset($data['inquirable_id']);
    }

    protected function loadPatientStatusForInquiries(Collection $inquiries): void
    {
        $patientStatuses = PatientStatus::query()
            ->select(['id', 'status', 'hex_color'])
            ->get();

        $inquiries->each(function ($inquiry) use ($patientStatuses) {
            if ($inquiry->isPatientCreated()) {
                $statusId = $inquiry['inquirable']['status_id'];

                $inquiry['inquirable']['status'] = $patientStatuses->first(function ($patientStatus) use ($statusId) {
                    return $patientStatus->id === $statusId;
                });
            } else {
                $inquiry['inquirable']['status'] = null;
            }
        });
    }

    protected function loadPatientProvidersForInquiries(Collection $inquiries): void
    {
        $patientsWithProviders = Patient::query()
            ->select('id')
            ->whereIn('id', $inquiries->where('inquirable_type', Patient::class)->pluck('inquirable_id'))
            ->with('providers:id,provider_name')
            ->get();

        $inquiries->each(function ($inquiry) use ($patientsWithProviders) {
            if ($inquiry->isPatientCreated()) {
                $inquirableId = $inquiry['inquirable']['id'];

                $inquiry['inquirable']['providers'] = $patientsWithProviders->first(function ($patientWithProviders) use ($inquirableId) {
                    return $patientWithProviders->id === $inquirableId;
                })['providers'];

                $inquiry['inquirable']['past_appointment_provider'] = $inquiry->getPastAppointmentProvider();
            } else {
                $inquiry['inquirable']['providers'] = null;
            }
        });
    }

    protected function loadLastDocumentAndDocumentRequestForInquiries(Collection $inquiries): void
    {
        $documents = PatientDocument::query()
            ->whereIn('patient_id', $inquiries->pluck('inquirable_id'))
            ->where('document_type_id', PatientDocumentType::getNewPatientId())
            ->where([
                ['document_type_id', '=', PatientDocumentType::getNewPatientId()],
            ])
            ->orderBy('created_at', 'DESC')
            ->get();

        $requiredFormTypes = Cache::rememberForever('patient-inquiry:required-form-types', function () {
            return PatientFormType::getFormTypeIds(self::REQUIRED_FORMS);
        });

        $documentRequests = PatientDocumentRequest::query()
            ->whereIn('patient_id', $inquiries->pluck('inquirable_id'))
            ->with(['items'])
            ->whereHas('items', function ($query) use ($requiredFormTypes) {
                $query->whereIn('form_type_id', $requiredFormTypes);
            })
            ->orderBy('created_at', 'DESC')
            ->get();

        $inquiries->each(function ($inquiry) use ($documents, $documentRequests) {
            $lastDocument = $documents->where('patient_id', $inquiry->inquirable_id)
                ->where('created_at', '>', $inquiry->created_at)
                ->first();

            $inquiry->inquirable->last_document = $lastDocument;

            $documentRequests = $documentRequests->where('patient_id', $inquiry->inquirable_id)
                ->where('created_at', '>', $inquiry->created_at);

            if (isset($lastDocument)) {
                $documentRequests = $documentRequests->filter(function ($documentRequest) use ($lastDocument) {
                    return $documentRequest->items->contains('id', $lastDocument->document_request_item_id);
                });
            }

            $inquiry->inquirable->last_document_request = $documentRequests->first();
        });
    }

    private function processInquiriesAppointments($inquiry): PatientInquiry
    {
        $nextAppointment = null;
        $rescheduledAppointment = null;

        foreach ($inquiry->inquirable->appointments as $appointment) {
            if (!$nextAppointment && $appointment->appointment_statuses_id === Status::getActiveId()) {
                $nextAppointment = $appointment;

                if (isset($appointment->rescheduled_appointment_id)) {
                    $rescheduledAppointment = $inquiry->inquirable->appointments()->where('id', $appointment->rescheduled_appointment_id)->first();
                }
            }
        }

        $inquiry->next_appointment = $nextAppointment;
        $inquiry->rescheduled_appointment = $rescheduledAppointment;
        unset($inquiry->inquirable->appointments);

        return $inquiry;
    }

    protected function getPatientDocumentUploader($id, $model){

        $documentUploader = null;

        $isPatientLeadDocument = $model === class_basename(PatientLeadDocument::class);
        if ($isPatientLeadDocument) {
            $documentModel = PatientLeadDocument::class;
        } else if ($model === class_basename(PatientElectronicDocument::class)) {
            $documentModel = PatientElectronicDocument::class;
        } else {
            $documentModel = 'App\\' . $model;
        }

        $patientDocument  = $documentModel::find($id);

        if ($patientDocument) {
            if ($isPatientLeadDocument) {
                $uploadInfo = PatientLeadDocumentUploadInfo::with(['user', 'user.meta', 'user.provider'])
                    ->where('patient_lead_document_id', '=', $id)
                    ->where('document_model', '=', $documentModel)
                    ->first();
            } else {
                $uploadInfo = PatientDocumentUploadInfo::with(['user', 'user.meta', 'user.provider'])
                    ->where('patient_document_id', '=', $id)
                    ->where('document_model', '=', $documentModel)
                    ->first();
            }

            if ($uploadInfo && $uploadInfo->user) {
                $documentUploader = $uploadInfo->user->isAdmin()
                    ? $uploadInfo->user->getFullname()
                    : optional($uploadInfo->user->provider)->provider_name;
            }
        }

        return $documentUploader;
    }

    protected function formatPhones(array &$data): void
    {
        if (!empty($data['cell_phone'])) {
            $data['cell_phone'] = format_phone($data['cell_phone']);
        }

        if (!empty($data['home_phone'])) {
            $data['home_phone'] = format_phone($data['home_phone']);
        }

        if (!empty($data['work_phone'])) {
            $data['work_phone'] = format_phone($data['work_phone']);
        }
    }

    protected function attachPatientTagReturningIfNeeded($inquiry, $inquirable, $isReturning): void
    {
        if ($isReturning
            && $inquiry->isPatientCreated()
            && $inquirable->appointments()
                ->whereIn('appointment_statuses_id', Status::getCompletedVisitCreatedStatusesId())
                ->exists()
        ) {
            $inquirable->attachTag(PatientTag::getReturningId());
        }
    }

    protected function detachPatientTagReturningIfNeeded(PatientInquiry $inquiry): void
    {
        if (!$inquiry->isPatientCreated()) {
            return;
        }

        $patient = $inquiry->inquirable()->first();
        $patient->detachTag(PatientTag::getReturningId());
    }
}
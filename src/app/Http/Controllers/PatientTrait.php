<?php

/**
 * Created by PhpStorm.
 * User: braginec_dv
 * Date: 29.06.2017
 * Time: 11:38
 */

namespace App\Http\Controllers;

use App\Appointment;
use App\AssessmentForm;
use App\Enums\Ringcentral\RingcentralCallStatus;
use App\Helpers\ImageHelper;
use App\Http\Requests\Patient\ChartDocuments;
use App\Models\GoogleMeetingCallLog;
use App\Models\Patient\PatientElectronicDocument;
use App\Patient;
use App\PatientAlert;
use App\PatientAssessmentForm;
use App\PatientDocument;
use App\PatientDocumentComment;
use App\PatientDocumentType;
use App\PatientDocumentUploadInfo;
use App\PatientNote;
use App\PatientStatus;
use App\Provider;
use App\Status;
use App\Models\Provider\SalaryTimesheetLateCancellation;
use App\Scopes\PatientDocuments\DocumentsForAllScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Events\PatientDocumentPreview;
use App\Models\TridiuumPatient;
use App\Models\TreatmentModality;
use App\PatientComment;
use Illuminate\Http\Request;

trait PatientTrait
{
    /**
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function getPatient($id)
    {
        $with = [
            'status',
            'providers' => function ($query) {
                $query->providerNames();
                $query->withTrashed();
            },
            'informationForm',
            'preprocessedBalance' => function ($query) {
                $query->select([
                    'patient_id',
                    'balance_after_transaction AS balance',
                ]);
            },
            'balance' => function ($query) {
                $query->select([
                    'patient_id',
                    'balance_after_transaction AS balance',
                ]);
            },
            'diagnoses',
        ];

        $patient = Patient::with($with)
            ->where('id', $id)
            ->firstOrFail();

        if (!Auth::user()->isAdmin()) {
            $provider = $patient->allProviders()
                ->withTrashed()
                ->where('id', Auth::user()->provider_id)
                ->first();
            if (is_null($provider)) {
                abort(403);
            }
        }

        $now = Carbon::now();
        $effStopDate = Carbon::parse($patient->eff_stop_date);
        $dateDiff = $now->diffInDays($effStopDate, false);
        $patient->is_eff_almost_overdue = isset($patient->insurancePlan) && $dateDiff <= $patient->insurancePlan->reauthorization_notification_days_count && $dateDiff >= 0;
        $patient->is_overdue = $dateDiff < 0;
        $patient->is_documents_uploading_allowed = $this->hasInitialAssessmentForm($patient->id)['response'];

        $patient->information_form_document_name = $patient->informationForm ? optional($patient->documents()
            ->where('document_type_id', PatientDocumentType::getNewPatientId())
            ->first())->aws_document_name : null;
            
        //@todo uncomment in the future
        $patient->is_tridiuum_patient = false;/*$patient->tridiuumAppointments()
            ->when(Auth::user()->provider_id, function($query, $providerId) {
                $query->where('provider_id', $providerId);
            })
            ->exists();*/
        $patient->tridiuum_patient_id = null; //optional($patient->tridiuumPatient)->external_id;

        return $patient;
    }

    public function getStatusesList(Request $request)
    {
        $this->validate($request, [
            'except' => 'nullable|array|max:10',
            'except.*' => 'string',
        ]);
        $except = $request->get('except') ?? [];
        return PatientStatus::query()
            ->select(['id', 'status', 'hex_color'])
            ->when(is_array($except), function ($query) use ($except) {
                $query->whereNotIn('status', $except);
            })
            ->orderBy('status')
            ->get();
    }

    public function getPatientAppointments($id)
    {
        if (!$this->isUserHasAccessRightsForPatient($id, null, true)) {
            abort(403);
        }

        $patientAppointments = Appointment::select([
            'appointments.id',
            'appointments.time',
            'appointments.start_completing_date',
            'appointments.visit_length',
            'appointments.providers_id',
            'appointments.appointment_statuses_id',
            'appointments.reschedule_sub_status_id',
            'providers.provider_name',
            'offices.office',
            'appointments.offices_id AS office_id',
            'appointments.office_room_id',
            'appointments.reason_for_visit',
            'appointments.note_on_paper',
            'appointments.notes',
            'appointments.custom_notes',
            'appointments.patient_requested_cancellation_at',
            'appointments.created_at',
        ])
            ->with([
                'status',
                'rescheduleSubStatus',
                'squareTransaction:id,appointment_id,amount_money',
                'lateCancellationTransaction:id,appointment_id,payment_amount',
                'officeallyTransaction:id,appointment_id,payment_amount,transaction_purpose_id',
            ])
            ->where('patients_id', $id)
            ->join('providers', 'providers.id', '=', 'appointments.providers_id')
            ->join('offices', 'offices.id', '=', 'appointments.offices_id')
            ->orderBy('time', 'asc')
            ->get();

        $isAdmin = auth()->user()->isAdmin();
        $now = Carbon::now();
        $upcomingAppointments = [];
        $pastAppointments = [];
        $patient = null;
        $activeStatusId = Status::getActiveId();
        $providerHasAccess = $this->isUserHasAccessRightsForPatient($id);
        $isCollectPaymentAvailableForUser = $isAdmin || ($providerHasAccess && optional(auth()->user()->provider)->is_collect_payment_available);
        $completedVisitCreatedStatuses = Status::getCompletedVisitCreatedStatusesId();
        $cancellationFeeStatuses = Status::getStatusesForCancellationFee();

        $appointmentIds = $patientAppointments->pluck('id');
        $rescheduledAppointmentsMapping = Appointment::query()
            ->whereIn('rescheduled_appointment_id', $appointmentIds)
            ->get()
            ->reduce(function ($carry, $item) {
                $carry[$item->rescheduled_appointment_id] = $item;
                return $carry;
            }, []);

        foreach ($patientAppointments as $appointment) {
            if (is_null($patient)) {
                $patient = $appointment->patient;
            }
            $date = Carbon::createFromTimestamp($appointment->time);
            $appointment->date = $date->format('l<b\r>m/d/Y');
            $appointment->formatted_time = $date->format('h:i a');
            $dateDiff = $now->diffInHours($date, false);
            $appointment->has_collect_payment_button = $isCollectPaymentAvailableForUser && is_null($appointment->squareTransaction) && ((in_array($appointment->appointment_statuses_id, $cancellationFeeStatuses) && !is_null($appointment->lateCancellationTransaction)) || (in_array($appointment->appointment_statuses_id, $completedVisitCreatedStatuses) && !is_null($appointment->officeallyTransaction)));
            $appointment->has_video_session_button = !$isAdmin && $providerHasAccess && $appointment->providers_id == auth()->user()->provider_id && $date->isCurrentDay() && $appointment->appointment_statuses_id == $activeStatusId;
            $appointment->has_complete_appointment_button = $appointment->patients_id != 1111 && ($providerHasAccess && ($isAdmin || $appointment->providers_id == auth()->user()->provider_id) && $date->copy()->startOfDay()->lt($now) && $appointment->appointment_statuses_id == $activeStatusId && is_null($appointment->start_completing_date));
            $appointment->has_reschedule_appointment_button = $appointment->patients_id != 1111 && ($providerHasAccess && ($isAdmin || $appointment->providers_id == auth()->user()->provider_id) && $appointment->appointment_statuses_id == $activeStatusId && is_null($appointment->start_completing_date));
            $appointment->has_cancel_appointment_button = $appointment->patients_id != 1111 && ($providerHasAccess && ($isAdmin || $appointment->providers_id == auth()->user()->provider_id) && $appointment->appointment_statuses_id == $activeStatusId && is_null($appointment->start_completing_date));
            $appointment->has_control_buttons = $appointment->patients_id != 1111 && ($isAdmin || ($providerHasAccess && $appointment->providers_id == auth()->user()->provider_id));

            $appointment->cancellation_fee = optional($appointment->lateCancellationTransaction)->payment_amount / 100;
            $appointment->copay = optional($appointment->officeallyTransaction)->payment_amount / 100;

            $rescheduledAppointment = $rescheduledAppointmentsMapping[$appointment->id] ?? null;
            $appointment->rescheduled_appointment_date = isset($rescheduledAppointment) ? Carbon::createFromTimestamp($rescheduledAppointment->time)->format('m/d/y') : null;

            if ($dateDiff >= 0) {
                //                array_unshift($upcomingAppointments, $appointment);
                $upcomingAppointments[] = $appointment;
            } else {
                array_unshift($pastAppointments, $appointment);
                //                $pastAppointments[] = $appointment;
            }
        }
        return [
            [
                'title' => 'Upcoming',
                'i' => 1,
                'appointments' => $upcomingAppointments,
            ],
            [
                'title' => 'Past',
                'i' => 2,
                'appointments' => $pastAppointments,
            ],
        ];
    }

    public function getPatientVisitCreatedAppointments($id)
    {
        if (!$this->isUserHasAccessRightsForPatient($id, null, true)) {
            abort(403);
        }

        $visitCreatedAppointments = $this->getVisitCreatedAppointments($id);

        return response($visitCreatedAppointments);
    }

    public function getPatientNotifications($id)
    {
        if (!$this->isUserHasAccessRightsForPatient($id, null, true)) {
            abort(403);
        }

        $alerts = PatientAlert::select([
            'patient_alerts.*',
            DB::raw("COALESCE(providers.provider_name, CONCAT(users_meta.firstname, ' ', users_meta.lastname)) AS recorded_by_name"),
        ])
            ->leftJoin('users', 'users.id', '=', 'patient_alerts.recorded_by')
            ->leftJoin('users_meta', 'users.id', '=', 'users_meta.user_id')
            ->leftJoin('providers', 'providers.id', '=', 'users.provider_id')
            ->where('patient_id', $id)
            ->orderBy('patient_alerts.date_created', 'desc')
            ->orderBy('patient_alerts.created_at', 'desc')
            ->get();

        foreach ($alerts as $alert) {
            $alert->formatted_date_created = Carbon::parse($alert->date_created)->format('m/d/Y');
        }
        
        return $alerts;
    }

    public function getPatientNotes($id)
    {
        $patient = $this->getPatient($id);

        return $patient->patientNotes;
    }

    public function getPatientDocuments($id)
    {
        $patient = $this->getPatient($id);
        if (Auth::user()->isAdmin()) {
            $patient->documents()->withoutGlobalScope(DocumentsForAllScope::class)->get();
        }

        return $patient->documents;
    }

    public function getNote($id)
    {
        $note = PatientNote::query()
            ->with([
                'appointment.status',
                'appointment.visit.diagnoses',
                'diagnoses',
                'unlockRequest',
            ])
            ->findOrFail($id);

        $uniqueId = $this->generateUniqueId();
        $note->isEditingAllowed = $this->isNoteEditingAllowed($id);
        $note->is_note = true;
        $note->uniqueId = $uniqueId;
        $note->diagnoses_editable = optional($note->appointment)->appointment_statuses_id !== Status::getVisitCreatedId();
        if (!is_null($note->date_of_service)) {
            $note->date_of_service = Carbon::parse($note->date_of_service)->format('m/d/Y');
            if ($note->appointment && $note->appointment->status) {
                $note->formatted_dos = $note->date_of_service . ' (' . $note->appointment->status->status . ')';
            } else {
                $note->formatted_dos = $note->date_of_service;
            }
        }
        event(new PatientDocumentPreview($note, true, $uniqueId));

        return $note;
    }

    public function getUnfinalizedNote($id)
    {
        $note = PatientNote::where('patients_id', $id)
            ->where('is_finalized', false)
            ->first();
        if ($note) {
            $note->is_note = true;
        }
        return $note;
    }

    /**
     * @TODO: refactor this method
     * @param ChartDocuments $request
     * @param                $id
     *
     * @return array
     */
    public function getPatientNotesWithDocuments(ChartDocuments $request, $id)
    {
        $response = [];
        $page = $request->has('page') ? (int)$request->get('page') : 1;

        $limit = 15;
        $pagination = [
            'current_page' => $page,
            'per_page' => $limit,
            'start_from' => $limit * ($page - 1),
            'next_page' => null,
        ];
        $types = $request->input('types') ?? [];
        if (empty($types)) {
            return [
                'data' => [],
                'meta' => [
                    'pagination' => $pagination,
                ],
            ];
        }
        $limitQuery = "LIMIT {$pagination['start_from']}, {$pagination['per_page']}";


        $patient = Patient::findOrFail($id);
        $query = "
            SELECT *
            FROM (
        ";
        $selects = [];
        if (in_array('PatientNote', $types)) {
            $selects[] = "
                SELECT 'PatientNote' AS model, `id`, `created_at`, `updated_at`, `finalized_at`, `provider_name`, NULL as `provider_name_from_appointment`, `diagnosis_icd_code`, `date_of_service`, `long_range_treatment_goal`,
                  `shortterm_behavioral_objective`, `additional_comments`, `plan`, `interventions`, `progress_and_outcome`, NULL AS `aws_document_name`,
                  NULL AS `original_document_name`, NULL AS `comment`, NULL AS `comment_type`, NULL AS `appointment_id`, NULL AS comment_metadata, NULL AS `full_admin_name`, NULL AS `assessment_doc`, NULL AS `other_document_type`,
                  NULL AS `document_type`, `is_finalized`, NULL AS `is_system_comment`, `start_editing_note_date` AS `start_editing_date`, 0 AS only_for_admin, 1 AS `is_commentable`, NULL AS `is_tridiuum_document`
                FROM `patient_notes`
                WHERE `patient_notes`.`deleted_at` IS NULL AND `patient_notes`.`patients_id` = $id
            ";
        }

        $initialAssessmentFormFilterQuery = '';
        $initialAssessmentDocumentFilterQuery = '';
        $initialAssessmentPrivateDocumentsFilterQuery = '';
        if (in_array('InitialAssessment', $types) || in_array('PatientDocument', $types) || in_array('PatientPrivateDocument', $types)) {
            $initialAssessmentFormTypes = implode(',', AssessmentForm::getFileTypeIDsLikeInitialAssessment());
            $initialAssessmentDocumentTypes = implode(',', PatientDocumentType::getFileTypeIDsLikeInitialAssessment());
            if (in_array('InitialAssessment', $types) && !in_array('PatientDocument', $types)) {
                $initialAssessmentFormFilterQuery = " AND `%s` IN ($initialAssessmentFormTypes)";
                $initialAssessmentDocumentFilterQuery = " AND `document_type_id` IN ($initialAssessmentDocumentTypes)";
            } else if (!in_array('InitialAssessment', $types) && (in_array('PatientDocument', $types) || in_array('PatientPrivateDocument', $types))) {
                $initialAssessmentFormFilterQuery = " AND `%s` NOT IN ($initialAssessmentFormTypes)";
                $initialAssessmentDocumentFilterQuery = " AND `document_type_id` NOT IN ($initialAssessmentDocumentTypes)";
            }
            if (in_array('PatientPrivateDocument', $types) && in_array('InitialAssessment', $types)) {
                $initialAssessmentPrivateDocumentsFilterQuery = " AND `document_type_id` NOT IN ($initialAssessmentDocumentTypes)";
            }
        }

        $patientDocumentsQuery = "
                SELECT 'PatientDocument' AS model, `patient_documents`.`id`, `patient_documents`.`created_at`, `patient_documents`.`updated_at`, NULL AS finalized_at, NULL AS provider_name, NULL as `provider_name_from_appointment`, NULL AS diagnosis_icd_code, NULL AS date_of_service, NULL AS long_range_treatment_goal, NULL AS shortterm_behavioral_objective, NULL AS additional_comments, NULL AS plan,
                  NULL AS interventions, NULL AS progress_and_outcome, `patient_documents`.`aws_document_name`, `patient_documents`.`original_document_name`, NULL AS `comment`, NULL AS `comment_type`, NULL AS `appointment_id`, NULL AS comment_metadata, NULL AS `full_admin_name`,
                  NULL AS `assessment_doc`, `patient_documents`.`other_document_type`, `patient_document_types`.`type` AS `document_type`, NULL AS `is_finalized`,
                  NULL AS `is_system_comment`, NULL AS `start_editing_date`, patient_documents.only_for_admin, 1 AS `is_commentable`, `patient_documents`.`is_tridiuum_document`
                FROM `patient_documents`
                JOIN `patient_document_types` ON `patient_documents`.`document_type_id` = `patient_document_types`.`id`
                WHERE `patient_documents`.`patient_id` = $id AND `patient_documents`.`visible` = 1 AND `patient_documents`.`deleted_at` IS NULL";

        /**
         * Only private documents
         */
        if (in_array('PatientPrivateDocument', $types) && !in_array('PatientDocument', $types) && Auth::user()->isAdmin()) {
            $selects[] = $patientDocumentsQuery . ' AND patient_documents.only_for_admin = 1' . $initialAssessmentPrivateDocumentsFilterQuery;
        }

        if (in_array('PatientDocument', $types) || in_array('InitialAssessment', $types)) {
            if ((!Auth::user()->isAdmin() && !Auth::user()->isInsuranceAudit()) || !in_array('PatientPrivateDocument', $types)) {
                $selects[] = $patientDocumentsQuery . $initialAssessmentDocumentFilterQuery . ' AND patient_documents.only_for_admin = 0';
            } else {
                $selects[] = $patientDocumentsQuery . $initialAssessmentDocumentFilterQuery;
            }

            $selects[] = "
                SELECT 'PatientElectronicDocument' AS model, `patient_electronic_documents`.`id`, `patient_electronic_documents`.`created_at`, `patient_electronic_documents`.`updated_at`, NULL AS finalized_at, `provider_id` AS `provider_name`, NULL as `provider_name_from_appointment`, `document_type_id` AS `diagnosis_icd_code`,
                  NULL AS `date_of_service`, `document_data` AS  `long_range_treatment_goal`,
                  NULL AS `shortterm_behavioral_objective`, NULL AS `additional_comments`, NULL AS `plan`, NULL AS `interventions`, NULL AS `progress_and_outcome`, NULL AS `aws_document_name`,
                  CONCAT(`assessment_forms`.`document_name`, '.docx') AS `original_document_name`, NULL AS `comment`, NULL AS `comment_type`, NULL AS `appointment_id`, NULL AS comment_metadata, NULL AS `full_admin_name`, NULL AS `assessment_doc`, NULL AS `other_document_type`,
                  NULL AS `document_type`, NULL AS `is_finalized`, NULL AS `is_system_comment`, `start_editing_date`, 0 AS only_for_admin, 1 AS `is_commentable`, NULL AS `is_tridiuum_document`
                FROM `patient_electronic_documents`
                LEFT JOIN `assessment_forms` ON `assessment_forms`.`id` = `patient_electronic_documents`.`document_type_id`
                WHERE `patient_electronic_documents`.`deleted_at` IS NULL AND `patient_id` = {$id}" . sprintf($initialAssessmentFormFilterQuery, 'document_type_id');
        }

        $patientCommentsQuery = "
            SELECT IF(`is_system_comment` = 0, 'PatientComment','PatientAlert') AS model, `patient_comments`.`id`, `patient_comments`.`created_at`, `patient_comments`.`updated_at`, NULL AS finalized_at, `provider_direct`.`provider_name` as `provider_name`, `provider_from_appointment`.`provider_name` as `provider_name_from_appointment`,
              NULL AS diagnosis_icd_code, NULL AS date_of_service, NULL AS long_range_treatment_goal, NULL AS shortterm_behavioral_objective, NULL AS additional_comments, NULL AS plan, NULL AS interventions, NULL AS progress_and_outcome, NULL AS aws_document_name, NULL AS original_document_name, IF(`default_comment_id` is null, `patient_comments`.`comment`, patient_default_comments.comment) AS comment,
              `patient_comments`.`comment_type` AS `comment_type`, `patient_comments`.`appointment_id` AS `appointment_id`, `patient_comments`.`metadata` AS `comment_metadata`, CONCAT(`users_meta`.`firstname`, ' ', `users_meta`.`lastname`) AS `full_admin_name`, NULL AS `assessment_doc`, NULL AS `other_document_type`,
              NULL AS `document_type`, NULL AS `is_finalized`, `is_system_comment`, NULL AS `start_editing_date`, `patient_comments`.`only_for_admin`, 0 AS `is_commentable`, NULL AS `is_tridiuum_document`
            FROM `patient_comments`
            LEFT JOIN `appointments` ON `appointments`.`id` = `patient_comments`.`appointment_id`
            LEFT JOIN `providers` AS provider_from_appointment ON provider_from_appointment.`id` = `appointments`.`providers_id`
            LEFT JOIN `providers` AS provider_direct ON provider_direct.`id` = `patient_comments`.`provider_id`
            LEFT JOIN `users_meta` ON `users_meta`.`user_id` = `patient_comments`.`admin_id`
            LEFT JOIN `patient_default_comments` ON `patient_default_comments`.`id` = `patient_comments`.`default_comment_id`
            WHERE `patient_id` = $id AND `patient_comments`.`deleted_at` IS NULL"; 
        
        if (!Auth::user()->isAdmin() || !in_array('PatientPrivateComment', $types)) {
            $patientCommentsQuery .= " AND `patient_comments`.`only_for_admin` != 1";
        }
        /**
         * Only private comments
         */
        if (in_array('PatientPrivateComment', $types) && !in_array('PatientComment', $types) && Auth::user()->isAdmin()) {
            $selects[] = $patientCommentsQuery . ' AND patient_comments.only_for_admin = 1';
        }

        if (in_array('PatientAlert', $types) && !in_array('PatientComment', $types)) {
            $selects[] = $patientCommentsQuery . ' AND is_system_comment = 1';
        } else if (in_array('PatientComment', $types) && !in_array('PatientAlert', $types)) {
            $selects[] = $patientCommentsQuery . ' AND is_system_comment = 0';
        } else if (in_array('PatientAlert', $types) && in_array('PatientComment', $types)) {
            $selects[] = $patientCommentsQuery;
        }

        if (in_array('CallLog', $types)) {
            $callLogsQuery = "
                SELECT 'CallLog' AS model, `ringcentral_call_logs`.`id`, COALESCE(`ringcentral_call_logs`.`call_starts_at`, `ringcentral_call_logs`.`created_at`) AS created_at, `ringcentral_call_logs`.`updated_at`, NULL AS finalized_at, `providers`.`provider_name`, NULL as `provider_name_from_appointment`,
                  NULL AS diagnosis_icd_code, NULL AS date_of_service, NULL AS long_range_treatment_goal, NULL AS shortterm_behavioral_objective, NULL AS additional_comments, NULL AS plan, NULL AS interventions, NULL AS progress_and_outcome, `ringcentral_call_logs`.`phone_from` AS `aws_document_name`, `ringcentral_call_logs`.`phone_to` AS `original_document_name`, `ringcentral_call_logs`.`comment` AS comment, NULL AS `comment_type`, NULL AS `appointment_id`, NULL AS comment_metadata,
                  IF(`providers`.`id` IS NULL, CONCAT(`users_meta`.`firstname`, ' ', `users_meta`.`lastname`), `providers`.`provider_name`) AS `full_admin_name`, NULL AS `assessment_doc`, NULL AS `other_document_type`,
                  `ringcentral_call_logs`.`call_status` AS `document_type`, `ringcentral_call_logs`.`call_status` AS `is_finalized`, `ringcentral_call_logs`.`call_ends_at` AS `is_system_comment`, `ringcentral_call_logs`.`call_starts_at` AS `start_editing_date`, `ringcentral_call_logs`.`only_for_admin`, 0 AS `is_commentable`, NULL AS `is_tridiuum_document`
                FROM `ringcentral_call_logs`
                LEFT JOIN `users` on `users`.`id` = `ringcentral_call_logs`.`user_id`
                LEFT JOIN `providers` ON `providers`.`id` = `users`.`provider_id`
                LEFT JOIN `users_meta` ON `users_meta`.`user_id` = `users`.`id`
                WHERE `ringcentral_call_logs`.`patient_id` = $id AND `ringcentral_call_logs`.`deleted_at` IS NULL";

            if (!Auth::user()->isAdmin()) {
                $callLogsQuery .= " AND `ringcentral_call_logs`.`only_for_admin` = 0";
            }

            $selects[] = $callLogsQuery;
        }

        if (in_array('TelehealthSession', $types)) {
            $selects[] = "
               SELECT 'TelehealthSession' AS model, `google_meeting_call_logs`.`id`, `google_meeting_call_logs`.`call_starts_at` AS created_at, `google_meeting_call_logs`.`updated_at`, NULL AS finalized_at, `providers`.`provider_name`, NULL as `provider_name_from_appointment`,
                  `google_meetings`.`id` AS diagnosis_icd_code, NULL AS date_of_service, `google_meeting_call_logs`.`duration` AS long_range_treatment_goal, NULL AS shortterm_behavioral_objective, NULL AS additional_comments, NULL AS plan, NULL AS interventions, NULL AS progress_and_outcome, NULL `aws_document_name`, NULL AS `original_document_name`, NULL AS comment, NULL AS `comment_type`,  NULL AS `appointment_id`, NULL AS comment_metadata,
                  NULL AS `full_admin_name`, NULL AS `assessment_doc`, NULL AS `other_document_type`,
                  NULL AS `document_type`, NUll AS `is_finalized`, NULL AS `is_system_comment`, NULL AS `start_editing_date`, 0 AS only_for_admin, 0 AS `is_commentable`, NULL AS `is_tridiuum_document`
                FROM `google_meetings`
                JOIN `google_meeting_call_logs` ON `google_meeting_call_logs`.`google_meeting_id` =`google_meetings`.`id`
                LEFT JOIN `providers` ON `providers`.`id` = `google_meetings`.`provider_id`
                WHERE `google_meeting_call_logs`.`is_initial` = 1 AND `google_meetings`.`patient_id` = $id AND `google_meeting_call_logs`.`deleted_at` IS NULL
            ";
        }

        $query .= implode(' UNION ALL ', $selects) . ") AS `documents` ORDER BY `documents`.`created_at` DESC, `documents`.`id` DESC {$limitQuery}";
        $data = DB::select(DB::raw($query));

        if (count($data) >= $pagination['per_page']) {
            $pagination['next_page'] = $pagination['current_page'] + 1;
        }

        foreach ($data as $key => $note) {
            if ($note->is_commentable) {
                $data[$key]->document_comments
                    = $this->getPatientDocumentComments($note->id, $note->model);
                $data[$key]->document_uploader = $this->getPatientDocumentUploader($note->id, $note->model);
                $data[$key]->default_address = $this->getPatientDocumentDefaultAddresses($note);
            }
            if ($data[$key]->model == 'PatientNote') {
                $data[$key]->fax_supported = true;
                $data[$key]->is_editing_allowed = $this->isNoteEditingAllowed($note->id);
            } else if ($data[$key]->model === 'PatientDocument') {
                $docName = $data[$key]->aws_document_name;
                //                if(Storage::disk('patients_docs')->exists($docName)) {
                //                    $preview = ImageHelper::getBase64ImageThumbnail($docName);
                //                    if (!empty($preview)) {
                //                        $data[$key]->preview = $preview;
                //                    }
                //                }
            } else if ($data[$key]->model === 'PatientAssessmentForm') {
                $data[$key]->is_editing_allowed = $this->isAssessmentEditingAllowed($data[$key]->start_editing_date);
            } else if ($data[$key]->model === 'PatientComment') {
                $data[$key]->is_system_comment = boolval($data[$key]->is_system_comment);

                $appointment = Appointment::withTrashed()->find($note->appointment_id);
                $data[$key]->appointment = $appointment;
                $data[$key]->comment_metadata = json_decode($note->comment_metadata);
                $data[$key]->new_appointment = $note->comment_type === PatientComment::RESCHEDULE_COMMENT_TYPE && isset($appointment)
                    ? Appointment::withTrashed()->where('rescheduled_appointment_id', $appointment->id)->first() 
                    : null;
            } else if ($data[$key]->model === 'PatientElectronicDocument') {

                $electronicDocumentData = json_decode($data[$key]->long_range_treatment_goal, true);
                $documentType = $this->getDocumentById($data[$key]->diagnosis_icd_code);

                if (array_key_exists('general_summary', $electronicDocumentData)) {
                    $data[$key]->general_summary = $electronicDocumentData['general_summary'];
                }
                if (array_key_exists('presenting_problem', $electronicDocumentData)) {
                    $data[$key]->presenting_problem = $electronicDocumentData['presenting_problem'];
                }
                if (array_key_exists('presenting_concerns', $electronicDocumentData)) {
                    $data[$key]->presenting_concerns = $electronicDocumentData['presenting_concerns'];
                }
                if (array_key_exists('diagnosis_icd_code', $electronicDocumentData)) {
                    $data[$key]->diagnosis_icd_code = $electronicDocumentData['diagnosis_icd_code'];
                }
                if (array_key_exists('long_term_goals', $electronicDocumentData)) {
                    $data[$key]->long_term_goals = $electronicDocumentData['long_term_goals'];
                }
                if (array_key_exists('short_term_goals', $electronicDocumentData)) {
                    $data[$key]->short_term_goals = $electronicDocumentData['short_term_goals'];
                }
                if (array_key_exists('treatment_plan', $electronicDocumentData)) {
                    $data[$key]->treatment_plan = $electronicDocumentData['treatment_plan'];
                }
                if (array_key_exists('date_of_service', $electronicDocumentData)) {
                    $data[$key]->date_of_service = $electronicDocumentData['date_of_service'];
                }
                if (array_key_exists('clinical_symptoms', $electronicDocumentData)) {
                    $data[$key]->clinical_symptoms = $electronicDocumentData['clinical_symptoms'];
                }
                if (array_key_exists('reason_for_discharge', $electronicDocumentData)) {
                    $data[$key]->reason_for_discharge = $electronicDocumentData['reason_for_discharge'];
                }
                if (array_key_exists('reason_for_referral', $electronicDocumentData)) {
                    $data[$key]->reason_for_referral = $electronicDocumentData['reason_for_referral'];
                }
                if (array_key_exists('clinical_indications', $electronicDocumentData)) {
                    $data[$key]->medical_need = $electronicDocumentData['clinical_indications']['medical_need'];
                }

                if (array_key_exists('therapy_reason', $electronicDocumentData)) {
                    $data[$key]->therapy_reason = $electronicDocumentData['therapy_reason'];
                }
                if (array_key_exists('additional_comments', $electronicDocumentData)) {
                    $data[$key]->additional_comments = $electronicDocumentData['additional_comments'];
                }
                if (array_key_exists('clinical_rationale_for_group_referral', $electronicDocumentData)) {
                    $data[$key]->clinical_rationale_for_group_referral = $electronicDocumentData['clinical_rationale_for_group_referral'];
                }

                $data[$key]->provider_name = optional($this->getProviderById($data[$key]->provider_name))->provider_name;
                $data[$key]->document_name = $documentType->document_name;
                $data[$key]->document_unique_id = $documentType->slug;
                $data[$key]->is_editing_allowed = $this->isElectronicDocumentEditingAllowed($data[$key]->start_editing_date);
            } elseif ($data[$key]->model === "CallLog") {
                $data[$key]->is_finalized = RingcentralCallStatus::getNameByStatus($data[$key]->is_finalized ?? '');
                $startDate = $data[$key]->start_editing_date;
                $endDate = $data[$key]->is_system_comment;
                if ($startDate && $endDate) {
                    $data[$key]->long_range_treatment_goal = Carbon::parse($endDate)->diffInSeconds(Carbon::parse($startDate));  //call duration
                } else {
                    $data[$key]->long_range_treatment_goal = null;  //call duration
                }
                $data[$key]->document_type = camelToTitle(RingcentralCallStatus::getNameByStatus($data[$key]->document_type ?? ''));
            } else if ($data[$key]->model === 'TelehealthSession') {
                $preparedCallLogs = [];
                $providerDuration = 0;
                GoogleMeetingCallLog::query()
                    ->where('google_meeting_id', $data[$key]->diagnosis_icd_code)
                    ->orderBy('call_starts_at')
                    ->each(function (GoogleMeetingCallLog $item) use (&$preparedCallLogs, &$providerDuration) {
                        $preparedCallLogs[] = [
                            'date' => $item->call_starts_at->toDateTimeString(),
                            'timestamp' => $item->call_starts_at->timestamp,
                            'action' => 'joined',
                            'caller_name' => $item->caller_name,
                        ];
                        $preparedCallLogs[] = [
                            'date' => $item->call_ends_at->toDateTimeString(),
                            'timestamp' => $item->call_ends_at->timestamp,
                            'action' => 'leave',
                            'caller_name' => $item->caller_name,
                        ];
                        if ($item->is_external == 0) {
                            $providerDuration += $item->duration;
                        }
                    });

                $data[$key]->human_readable_duration = seconds_to_human_readable_duration($providerDuration);
                $data[$key]->child_call_logs = array_values(array_sort($preparedCallLogs, function ($item) {
                    return $item['timestamp'];
                }));
            }

            $data[$key]->fax_supported = $data[$key]->model === 'PatientNote' || in_array(pathinfo($data[$key]->original_document_name, PATHINFO_EXTENSION), config('ringcentral.supported_formats'));
        }

        $response['data'] = $data;
        $response['meta'] = [
            'pagination' => $pagination
        ];

        return $response;
    }

    public function getPatientDocumentUploader($id, $model)
    {

        $documentUploader = null;

        if ($model == 'PatientElectronicDocument') {

            $documentModel = PatientElectronicDocument::class;
        } else {

            $documentModel = 'App\\' . $model;
        }

        $patientDocument  = $documentModel::find($id);

        if ($patientDocument) {
            $uploadInfo = PatientDocumentUploadInfo::with(['user', 'user.meta', 'user.provider'])
                ->where('patient_document_id', '=', $id)
                ->where('document_model', '=', $documentModel)
                ->first();
            if ($uploadInfo && $uploadInfo->user) {
                if ($uploadInfo->user->isAdmin()) {
                    $documentUploader = $uploadInfo->user->getFullname();
                } else {
                    if ($documentUploader = $uploadInfo->user->provider) {
                        $documentUploader = $uploadInfo->user->provider->provider_name;
                    } else {
                        return '';
                    }
                }
            }
        }

        return $documentUploader;
    }

    public function getPatientDocumentComments($id, $model)
    {
        $documentComments = null;

        if ($model == 'PatientElectronicDocument') {
            $documentModel = PatientElectronicDocument::class;
        } else {
            $documentModel = 'App\\' . $model;
        }

        if ($model === 'PatientDocument') {
            $patientDocument = $documentModel::where('id', $id)
                ->withoutGlobalScope(DocumentsForAllScope::class)
                ->first();
        } else {
            $patientDocument = $documentModel::find($id);
        }
        
        if ($patientDocument) {
            $documentComments = PatientDocumentComment::where('patient_documents_id', '=', $id)
                ->where('document_model', '=', $documentModel)
                ->leftJoin(
                    'providers',
                    'providers.id',
                    '=',
                    'patient_document_comments.provider_id'
                )
                ->leftJoin(
                    'users_meta',
                    'users_meta.user_id',
                    '=',
                    'patient_document_comments.admin_id'
                )
                ->orderBy('patient_document_comments.created_at', 'asc')
                ->get([
                    'patient_document_comments.id',
                    'content',
                    'provider_name',
                    'firstname',
                    'lastname',
                    'patient_document_comments.created_at',
                    'is_system_comment'
                ]);
        }

        return $documentComments;
    }

    public function getPatientDocumentDefaultAddresses($document)
    {
        $addresses = [];
        $id = $document->id;
        if ($document->model === 'PatientAssessmentForm') {
            $patientDocument = PatientAssessmentForm::where('id', '=', $id)
                ->first();

            $documentType = PatientDocumentType::where('type', '=', $patientDocument->assessmentFormTemplate->title)
                ->first();
        } else if ($document->model === 'PatientDocument') {
            $patientDocument  = PatientDocument::withoutGlobalScope(DocumentsForAllScope::class)
                ->where('id', '=', $id)
                ->with('documentType')
                ->first();
            $documentType = $patientDocument->documentType;
        }

        if (isset($patientDocument) && isset($documentType->defaultAddress)) {
            $addresses['email'] = $documentType->defaultAddress->email;
            $addresses['fax'] = $documentType->defaultAddress->fax;
        }

        return $addresses;
    }

    public function getAllPatients()
    {
        $patients = Patient::all();

        return $patients;
    }

    public function isNoteEditingAllowed($id)
    {
        $note = PatientNote::select([
            'start_editing_note_date',
            'is_finalized',
        ])->where('id', $id)->firstOrFail();

        $now = Carbon::now();

        $noteCreatedDate = Carbon::parse($note->start_editing_note_date);
        $hoursdiff = $noteCreatedDate->diffInHours($now, false);
        $allowed = false;
        $hours = 0;
        if ($hoursdiff <= config('app.allowed_note_editing_depth')) {
            $allowed = true;
            $hours = config('app.allowed_note_editing_depth') - $hoursdiff;
        }
        if ($hours <= 0) {
            $hours = 0;
            $allowed = false;
        }
        if (!$note->is_finalized) {
            $allowed = true;
        }

        return [
            'allowed' => $allowed,
            'hours' => $hours,
        ];
    }

    public function isAssessmentEditingAllowed($startEditingDate)
    {

        $startEditingDate = Carbon::createFromTimestamp(strtotime($startEditingDate));
        $now = Carbon::now();
        $hourdiff = $startEditingDate->diffInHours($now, false);
        $allowed = false;
        $hours = 0;
        if ($hourdiff >= 0) {
            if ($hourdiff <= config('app.allowed_assessment_editing_depth')) {
                $allowed = true;
                $hours = config('app.allowed_assessment_editing_depth') - $hourdiff;
            }
        } else {
            $hours = 0;
            $allowed = false;
        }

        if ($hours <= 0) {
            $hours = 0;
            $allowed = false;
        }

        return [
            'allowed' => $allowed,
            'hours' => $hours,
        ];
    }

    public function getProviderById($id)
    {

        $provider = Provider::withTrashed()->where('id', $id)->first();

        return $provider;
    }

    public function getDocumentById($id)
    {

        $document = AssessmentForm::find($id);

        return $document;
    }

    public function generateUniqueId()
    {

        return md5(uniqid(rand(), true));
    }

    public function getProviderSignature($providerId)
    {

        $signatureBase64 = null;

        $user = Provider::findOrFail($providerId)->user;
        $signature = $user->signature->signature;

        if (Storage::disk('signatures')->exists($signature)) {
            $signatureBase64 = ImageHelper::getBase64ImageThumbnail($signature, true, 'signatures');
        }

        return $signatureBase64;
    }

    public function getSessionTime($appointment) {
        $date = Carbon::createFromTimestamp($appointment->time);
        $startTime = $date->format('g:i A');
        $appointmentLogTime = $appointment->getTimeFromLogs();

        if (isset($appointmentLogTime)) {
            $startTime = $appointmentLogTime['start_time'];
        }

        $currentTreatmentModality = TreatmentModality::find($appointment->treatment_modality_id);
        $duration = isset($currentTreatmentModality) ? $currentTreatmentModality->duration : 60;

        $endTime = Carbon::createFromFormat('g:i A', $startTime)->addMinutes($duration)->format('g:i A');

        return [
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];
    }

    private function getVisitCreatedAppointments($id)
    {
        $statuses = Status::getCompletedVisitCreatedStatusesId();

        $patientAppointments = Appointment::select([
            'appointments.id',
            'appointments.time',
            'appointments.is_initial',
            'appointments.visit_length',
            'appointments.patients_id',
            'appointments.providers_id',
            'appointments.initial_assessment_type',
            'appointments.initial_assessment_id',
            'providers.provider_name',
            'offices.office',
            'appointments.reason_for_visit',
            'appointments.note_on_paper',
            'appointment_statuses.status',
            'appointment_statuses.id AS appt_status_id',
        ])
            ->where('appointments.patients_id', $id)
            ->whereIn('appointments.appointment_statuses_id', $statuses)
            ->join('providers', 'providers.id', '=', 'appointments.providers_id')
            ->join('offices', 'offices.id', '=', 'appointments.offices_id')
            ->join('appointment_statuses', 'appointment_statuses.id', '=', 'appointments.appointment_statuses_id')
            ->orderBy('time')
            ->with(['patient'])
            ->get();

        $visitCreatedAppointments = [];

        $patient = null;
        foreach ($patientAppointments as $appointment) {
            if (is_null($patient)) {
                $patient = $appointment->patient;
            }
            $date = Carbon::createFromTimestamp($appointment->time);
            $appointment->date = $date->format('l<b\r>m/d/Y');
            $appointment->formatted_time = $date->format('h:i a');
            $appointment->actual_visit_length = $this->getVisitDurationFromLogs($appointment);

            $progressNote = PatientNote::where('patients_id', $id)
                ->where('appointment_id', '=', $appointment->getKey())
                ->first();
            $noteExistsData = [
                'title' => '',
                'electronic' => false,  //true if progress note exists
                'note_id' => null,
                'on_paper' => !!$appointment->note_on_paper,
                'saving' => false,
                'finalized' => true,   //progress note finalized

                'initial_assessment_model' => null,
                'initial_assessment_id' => null,
                'has_initial_assessment' => false,
                'is_initial_assessment_electronic' => false,
                'initial_assessment_type_slug' => null,
                'initial_assessment_file_name' => null,
            ];
            if ($progressNote) {
                $noteExistsData['electronic'] = true;
                $noteExistsData['note_id'] = $progressNote->getKey();
                $noteExistsData['finalized'] = !!$progressNote->is_finalized;
                if ($noteExistsData['finalized']) {
                    $noteExistsData['title'] = 'Electronic Version';
                } else {
                    $noteExistsData['title'] = 'Not Finalized';
                }
            } else if ($appointment->initial_assessment_id && $appointment->initial_assessment_type) {
                $noteExistsData['initial_assessment_model'] = $appointment->initial_assessment_type;
                $noteExistsData['initial_assessment_id'] = $appointment->initial_assessment_id;
                $noteExistsData['has_initial_assessment'] = true;
                $noteExistsData['title'] = 'Initial Assessment';
                if ($appointment->initial_assessment_type === PatientElectronicDocument::class) {
                    $noteExistsData['is_initial_assessment_electronic'] = true;
                    $noteExistsData['initial_assessment_type_slug'] = optional(optional(PatientElectronicDocument::query()->find($appointment->initial_assessment_id))->type)->slug;
                } else if ($appointment->initial_assessment_type === PatientDocument::class) {
                    $noteExistsData['initial_assessment_file_name'] = optional(PatientDocument::query()->find($appointment->initial_assessment_id))->aws_document_name;
                }
            } else if ($appointment->note_on_paper) {
                $noteExistsData['title'] = 'Exists On Paper';
            } else {
                $noteExistsData['title'] = 'Does Not Exist';
            }
            $appointment->note_exists = $noteExistsData;
            array_unshift($visitCreatedAppointments, $appointment);

            $user = Auth::user();
            $is_admin = $user->isAdmin();
            $has_patient = $this->isUserHasAccessRightsForPatient($appointment->patients_id, $user);
            $is_read_only_mode = !$is_admin && !$has_patient;
            $is_tridiuum_patient = TridiuumPatient::where('internal_id', $appointment->patients_id)->exists();

            $appointment->show_initial_assessment_electronic_button = !!$appointment->note_exists['initial_assessment_id'] &&
                $appointment->note_exists['is_initial_assessment_electronic'];
            $appointment->show_initial_assessment_file_button = !!$appointment->note_exists['initial_assessment_file_name'];
            $appointment->show_add_initial_assessment_button = !$is_admin
                && $appointment->is_initial
                && !$appointment->initial_assessment_id
                && $user->provider_id === $appointment->providers_id;
            $appointment->show_sync_initial_assessment_from_tridiuum_button = ($is_admin || $user->provider_id === $appointment->providers_id)
                && $appointment->is_initial
                && !$appointment->initial_assessment_id
                && $is_tridiuum_patient;

            $appointment->show_open_electronic_note_button = !$is_read_only_mode && $appointment->note_exists['electronic'] &&
                $appointment->note_exists['finalized'];
            $appointment->show_add_progress_note_button = !$is_admin &&
                $user->provider_id === $appointment->providers_id &&
                !$appointment->note_exists['electronic'] &&
                !$appointment->note_exists['on_paper'] &&
                !$appointment->note_exists['has_initial_assessment'] &&
                $appointment->note_exists['finalized'];
            $appointment->show_provider_finalize_button = !$is_admin &&
                $user->provider_id === $appointment->providers_id &&
                !$appointment->note_exists['finalized'];
            $appointment->show_admin_finalize_button = $is_admin &&
                !$appointment->note_exists['finalized'];
        }

        return $visitCreatedAppointments;
    }

    private function getVisitDurationFromLogs(Appointment $appointment): int
    {
        $ringCentralDuration = $this->getRingCentralCallLogsDuration($appointment);
        $googleMeetDuration = $this->getGoogleMeetCallLogsDuration($appointment);

        return $ringCentralDuration + $googleMeetDuration;
    }

    private function getRingCentralCallLogsDuration(Appointment $appointment): int
    {
        $duration = 0;

        $appointment->ringcentralCallLogs()->each(function ($log) use (&$duration) {
            if (empty($log->call_starts_at) || empty($log->call_ends_at)) {
                return;
            }

            $callStartTime = Carbon::createFromFormat("Y-m-d H:i:s", $log->call_starts_at);
            $callEndTime = Carbon::createFromFormat("Y-m-d H:i:s", $log->call_ends_at);

            $duration += $callEndTime->diffInMinutes($callStartTime);
        });

        return $duration;
    }

    private function getGoogleMeetCallLogsDuration(Appointment $appointment): int
    {
        $duration = 0;
        $googleMeeting = $appointment->googleMeet;

        if (isset($googleMeeting)) {
            $googleMeeting->callLogs()
                ->where('is_external', false)
                ->each(function ($log) use (&$duration) {
                    $duration += round($log->duration / 60);
                });
        }

        return $duration;
    }

    private function getVisitStartTimeFromLogs(Appointment $appointment): ?string
    {
        $googleMeetStartTime = $this->getGoogleMeetCallLogsStartTime($appointment);
        $ringCentralStartTime = $this->getRingCentralCallLogsStartTime($appointment);

        if ($googleMeetStartTime === null) {
            return $ringCentralStartTime;
        }

        if ($ringCentralStartTime === null) {
            return $googleMeetStartTime;
        }

        return Carbon::parse($googleMeetStartTime)->lt(Carbon::parse($ringCentralStartTime)) ? $googleMeetStartTime : $ringCentralStartTime;
    }

    private function getVisitEndTimeFromLogs(Appointment $appointment): ?string
    {
        $googleMeetEndTime = $this->getGoogleMeetCallLogsEndTime($appointment);
        $ringCentralEndTime = $this->getRingCentralCallLogsEndTime($appointment);

        if ($googleMeetEndTime === null) {
            return $ringCentralEndTime;
        }

        if ($ringCentralEndTime === null) {
            return $googleMeetEndTime;
        }

        return Carbon::parse($googleMeetEndTime)->gt(Carbon::parse($ringCentralEndTime)) ? $googleMeetEndTime : $ringCentralEndTime;
    }

    private function getRingCentralCallLogsStartTime(Appointment $appointment): ?string
    {
        return $appointment->ringcentralCallLogs()->min('call_starts_at');
    }

    private function getGoogleMeetCallLogsStartTime(Appointment $appointment): ?string
    {
        if ($appointment->googleMeet) {
            return $appointment->googleMeet->callLogs()->min('call_starts_at');
        }

        return null;
    }

    private function getRingCentralCallLogsEndTime(Appointment $appointment): ?string
    {
        return $appointment->ringcentralCallLogs()->max('call_ends_at');
    }

    private function getGoogleMeetCallLogsEndTime(Appointment $appointment): ?string
    {
        if ($appointment->googleMeet) {
            return $appointment->googleMeet->callLogs()->max('call_ends_at');
        }

        return null;
    }
}

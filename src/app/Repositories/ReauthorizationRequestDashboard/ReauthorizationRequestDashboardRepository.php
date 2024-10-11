<?php

namespace App\Repositories\ReauthorizationRequestDashboard;

use App\AssessmentForm;
use App\Models\Patient\PatientElectronicDocument;
use App\PatientDocument;
use App\Models\FutureInsuranceReauthorizationData;
use App\Models\SubmittedReauthorizationRequestForm;
use App\Models\SubmittedReauthorizationRequestFormLog;
use App\Models\SubmittedReauthorizationRequestFormStage;
use App\Models\SubmittedReauthorizationRequestFormStageChangeHistory;
use App\Patient;
use App\PatientDocumentType;
use App\PatientInsurancePlan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReauthorizationRequestDashboardRepository implements ReauthorizationRequestDashboardRepositoryInterface
{
    public function getUpcomingReauthorizationRequests(array $filters): array
    {
        $episodeStartDateSql = "SELECT episode_start_date FROM upcoming_reauthorization_requests urr WHERE urr.patient_id=patients.id AND urr.deleted_at IS NULL ORDER BY episode_start_date DESC LIMIT 1";

        $patientsQuery = Patient::query()
            ->select([
                'patients.*',
                DB::raw("($episodeStartDateSql) as episode_start_date"),
                DB::raw("DATEDIFF(patients.eff_stop_date, '" . Carbon::now() . "') AS date_diff"),
            ])
            ->join('patient_statuses', 'patients.status_id', '=', 'patient_statuses.id')
            ->join('patient_insurances_plans', 'patients.insurance_plan_id', '=', 'patient_insurances_plans.id')
            ->with('insurancePlan', 'insuranceReauthorizationRequestForms')
            ->whereNotNull('patients.insurance_plan_id')
            ->where('patient_insurances_plans.is_verification_required', true)
            ->havingRaw('episode_start_date IS NOT NULL')
            ->with([
                'status',
                'lastAppointment.provider' => function ($query) {
                    $query->providerNames();
                }
            ]);

        if (isset($filters['provider_id'])) {
            $patientsQuery->whereHas('lastAppointment.provider', function ($query) use ($filters) {
                $query->where('id', $filters['provider_id']);
            });
        }

        if (isset($filters['patient_statuses'])) {
            $patientsQuery->whereIn('status_id', $filters['patient_statuses']);
        }

        if (isset($filters['search_text'])) {
            $patientsQuery->where(DB::raw("CONCAT(patients.first_name, ' ', patients.last_name)"), 'like', '%' . $filters['search_text'] . '%');
        }

        if (isset($filters['expiration'])) {
            $expirations = $filters['expiration'];
            $patientsQuery
                ->where(function ($subQuery) use ($expirations) {
                    $doesntExpireId = PatientInsurancePlan::EXPIRATION_DOESNT_EXPIRE_ID;
                    $expiringSoonId = PatientInsurancePlan::EXPIRATION_EXPIRING_SOON_ID;
                    $expiredId = PatientInsurancePlan::EXPIRATION_EXPIRED_ID;

                    foreach ($expirations as $expiration) {
                        if ((int) $expiration === $doesntExpireId) {
                            $subQuery->orWhere(function ($orSubQuery) {
                                $orSubQuery
                                    ->whereNotNull('patients.auth_number')
                                    ->whereNotNull('patients.eff_stop_date')
                                    ->whereNotNull('patients.visits_auth_left')
                                    ->where(DB::raw("DATEDIFF(patients.eff_stop_date, '" . Carbon::now() . "')"), '>', DB::raw('patient_insurances_plans.reauthorization_notification_days_count'))
                                    ->where('patients.visits_auth_left', '>', DB::raw('patient_insurances_plans.reauthorization_notification_visits_count'));
                            });
                        } elseif ((int) $expiration === $expiringSoonId) {
                            $subQuery
                                ->orWhere(function ($orSubQuery) {
                                    $orSubQuery
                                        ->whereNotNull('patients.auth_number')
                                        ->whereNotNull('patients.eff_stop_date')
                                        ->whereNotNull('patients.visits_auth_left')
                                        ->where(DB::raw("DATEDIFF(patients.eff_stop_date, '" . Carbon::now() . "')"), '>', 0)
                                        ->where('patients.visits_auth_left', '>', 0)
                                        ->where(function ($orSubQuery) {
                                            $orSubQuery
                                                ->orWhere(DB::raw("DATEDIFF(patients.eff_stop_date, '" . Carbon::now() . "')"), '<', DB::raw('patient_insurances_plans.reauthorization_notification_days_count'))
                                                ->orWhere('patients.visits_auth_left', '<', DB::raw('patient_insurances_plans.reauthorization_notification_visits_count'));
                                        });
                                });
                        } elseif ((int) $expiration === $expiredId) {
                            $subQuery
                                ->orWhereNull('patients.auth_number')
                                ->orWhereNull('patients.eff_stop_date')
                                ->orWhereNull('patients.visits_auth_left')
                                ->orWhere(DB::raw("DATEDIFF(patients.eff_stop_date, '" . Carbon::now() . "')"), '<=', 0)
                                ->orWhere('patients.visits_auth_left', '<=', 0);
                        }
                    }
                });
        }

        $patientsQuery->havingRaw("date_diff > " . config('app.eff_stop_date_depth'))
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc');

        $data = $patientsQuery->get();

        $this->loadReauthorizationRequestDocument($data);

        return [
            'data' => $data,
            'meta' => [
                'total' => $data->count(),
            ]
        ];
    }

    public function getSubmittedReauthorizationRequestForms(array $filters): array
    {
        $data = [];

        $this->addPatientWithDocumentReauthorizationRequestForms($data, $filters);
        $this->addPatientWithoutDocumentReauthorizationRequestForms($data, $filters);

        // Sort by id desc
        usort($data, function ($a, $b) {
            return $b['id'] - $a['id'];
        });

        return [
            'data' => $data,
            'meta' => [
                'total' => count($data),
            ]
        ];
    }

    private function addPatientWithDocumentReauthorizationRequestForms(array &$data, array $filters): void
    {
        $requestFormsQuery = SubmittedReauthorizationRequestForm::select('submitted_reauthorization_request_forms.*')
            ->whereNotNull('document_id')
            ->join('patients', 'patients.id', '=', 'submitted_reauthorization_request_forms.patient_id')
            ->join('users', 'users.id', '=', 'submitted_reauthorization_request_forms.submitted_by')
            ->join('users_meta', 'users_meta.user_id', '=', 'users.id')
            ->leftJoin('providers', 'providers.id', '=', 'users.provider_id')
            ->with([
                'document',
                'patient',
                'submitter',
                'logs' => function ($query) {
                    $query->orderBy('id', 'desc');
                },
                'futureInsuranceReauthorizationData',
                'patient.status',
                'patient.insurance',
                'patient.insurancePlan',
                'submitter.meta',
                'submitter.provider',
                'document.documentShared' => function ($query) {
                    $query->orderBy('id', 'desc');
                },
                'document.documentShared.sharedMethod',
                'stageChangeHistory' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'stageChangeHistory.oldStage:id,name',
                'stageChangeHistory.newStage:id,name',
                'stageChangeHistory.user' => function ($query) {
                    $query->withTrashed()->select(['id']);
                },
                'stageChangeHistory.user.meta' => function ($query) {
                    $query->withTrashed()->select(['user_id','firstname','lastname']);
                },
            ]);

        if (isset($filters['submitted_by'])) {
            $requestFormsQuery->where('providers.id', 'like', '%' . $filters['submitted_by'] . '%');
        }
        if (isset($filters['search_text'])) {
            $requestFormsQuery->where(DB::raw('CONCAT(patients.first_name, " ", patients.last_name)'), 'like', '%' . $filters['search_text'] . '%');
        }
        if (isset($filters['patient_statuses'])) {
            $requestFormsQuery->whereIn('patients.status_id', $filters['patient_statuses']);
        }
        if (isset($filters['stages'])) {
            $requestFormsQuery->whereIn('submitted_reauthorization_request_forms.stage_id', $filters['stages']);
        }

        $requestFormsQuery->each(function ($form) use (&$data) {
            $data[] = $form;
        });
    }

    private function addPatientWithoutDocumentReauthorizationRequestForms(array &$data, array $filters): void
    {
        $requestFormsQuery = SubmittedReauthorizationRequestForm::select('submitted_reauthorization_request_forms.*')
            ->whereNull('document_id')
            ->join('patients', 'patients.id', '=', 'submitted_reauthorization_request_forms.patient_id')
            ->join('users', 'users.id', '=', 'submitted_reauthorization_request_forms.submitted_by')
            ->join('users_meta', 'users_meta.user_id', '=', 'users.id')
            ->leftJoin('providers', 'providers.id', '=', 'users.provider_id')
            ->with([
                'patient',
                'submitter',
                'logs' => function ($query) {
                    $query->orderBy('id', 'desc');
                },
                'futureInsuranceReauthorizationData',
                'patient.status',
                'patient.insurance',
                'patient.insurancePlan',
                'submitter.meta',
                'submitter.provider',
                'stageChangeHistory' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'stageChangeHistory.oldStage:id,name',
                'stageChangeHistory.newStage:id,name',
                'stageChangeHistory.user' => function ($query) {
                    $query->withTrashed()->select(['id']);
                },
                'stageChangeHistory.user.meta' => function ($query) {
                    $query->withTrashed()->select(['user_id','firstname','lastname']);
                },
            ]);

        if (isset($filters['submitted_by'])) {
            $requestFormsQuery->where('providers.id', 'like', '%' . $filters['submitted_by'] . '%');
        }
        if (isset($filters['search_text'])) {
            $requestFormsQuery->where(DB::raw('CONCAT(patients.first_name, " ", patients.last_name)'), 'like', '%' . $filters['search_text'] . '%');
        }
        if (isset($filters['patient_statuses'])) {
            $requestFormsQuery->whereIn('patients.status_id', $filters['patient_statuses']);
        }
        if (isset($filters['stages'])) {
            $requestFormsQuery->whereIn('submitted_reauthorization_request_forms.stage_id', $filters['stages']);
        }

        $requestFormsQuery->each(function ($form) use (&$data) {
            $data[] = $form;
        });
    }

    public function getStages(): Collection
    {
        return SubmittedReauthorizationRequestFormStage::query()
            ->orderBy('order')
            ->get();
    }

    public function changeStage(SubmittedReauthorizationRequestForm $form, array $data): SubmittedReauthorizationRequestForm
    {
        $updatingData = [
            'stage_id' => $data['stage_id'],
            'stage_changed_at' => Carbon::now(),
            'comment' => $data['comment'] ?? '',
        ];

        if ($data['stage_id'] !== SubmittedReauthorizationRequestFormStage::getApprovalReceivedId()) {
            $form->futureInsuranceReauthorizationData()->delete();
        }

        SubmittedReauthorizationRequestFormStageChangeHistory::create([
            'form_id' => $form->id,
            'old_stage_id' => $form->stage_id,
            'new_stage_id' => $data['stage_id'],
            'user_id' => auth()->id(),
            'comment' => $data['comment'] ?? '',
        ]);

        $form->update($updatingData);

        $form->load([
            'patient',
            'submitter',
            'logs' => function ($query) {
                $query->orderBy('id', 'desc');
            },
            'futureInsuranceReauthorizationData',
            'patient.status',
            'patient.insurance',
            'patient.insurancePlan',
            'submitter.meta',
            'submitter.provider',
            'stageChangeHistory' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'stageChangeHistory.oldStage:id,name',
            'stageChangeHistory.newStage:id,name',
            'stageChangeHistory.user' => function ($query) {
                $query->withTrashed()->select(['id']);
            },
            'stageChangeHistory.user.meta' => function ($query) {
                $query->withTrashed()->select(['user_id','firstname','lastname']);
            },
        ]);
        
        return $form;
    }

    public function createReauthorizationRequestFormWithoutDocument(int $patientId): SubmittedReauthorizationRequestForm
    {
        $readyToSendId = SubmittedReauthorizationRequestFormStage::getReadyToSendId();

        $requestForm = SubmittedReauthorizationRequestForm::create([
            'patient_id' => $patientId,
            'submitted_by' => Auth::user()->id,
            'stage_id' => $readyToSendId,
        ]);

        $requestForm->load(['patient', 'patient.status', 'patient.insurance', 'patient.insurancePlan']);

        return $requestForm;
    }

    public function createLog(SubmittedReauthorizationRequestForm $form, array $data): SubmittedReauthorizationRequestFormLog
    {
        return $form->logs()->create([
            'log_type' => $data['log_type'],
            'comment' => $data['comment']
        ]);
    }

    public function saveFutureInsuranceReauthorizationData(SubmittedReauthorizationRequestForm $form, array $data): FutureInsuranceReauthorizationData
    {
        return $form->futureInsuranceReauthorizationData()->create([
            'auth_number' => $data['auth_number'],
            'visits_auth' => $data['visits_auth'],
            'eff_start_date' => $data['eff_start_date'],
            'eff_stop_date' => $data['eff_stop_date'],
        ]);
    }

    public function loadReauthorizationRequestDocument(Collection $patients): void
    {
        $patientIds = $patients->pluck('id');
        $reauthorizationPatientDocumentIds = PatientDocumentType::getFileTypeIDsLikeReauthorization();
        $reauthorizationPatientElectronicDocumentIds = AssessmentForm::getFileTypeIDsLikeReauthorization();

        $patientDocuments = PatientDocument::query()
            ->whereIn('patient_id', $patientIds)
            ->whereIn('document_type_id', $reauthorizationPatientDocumentIds)
            ->get();

        $patientElectronicDocuments = PatientElectronicDocument::query()
            ->whereIn('patient_id', $patientIds)
            ->whereIn('document_type_id', $reauthorizationPatientElectronicDocumentIds)
            ->get();

        $patients->each(function ($patient) use ($patientDocuments, $patientElectronicDocuments) {
            $patientDocument = $patientDocuments
                ->where('patient_id', $patient->id)
                ->where('created_at', '>', $patient->episode_start_date)
                ->first();
            $patientElectronicDocument = $patientElectronicDocuments
                ->where('patient_id', $patient->id)
                ->where('created_at', '>', $patient->episode_start_date)
                ->first();

            if (empty($patientDocument)) {
                $document = $patientElectronicDocument;
            } elseif (empty($patientElectronicDocument)) {
                $document = $patientDocument;
            } else {
                $document = $patientDocument->created_at > $patientElectronicDocument->created_at
                    ? $patientDocument
                    : $patientElectronicDocument;
            }

            $patient->reauhtorization_request_document = $document ?? null;
        });
    }
}

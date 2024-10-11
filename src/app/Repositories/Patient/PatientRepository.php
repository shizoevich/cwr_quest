<?php

namespace App\Repositories\Patient;

use App\Appointment;
use App\AssessmentForm;
use App\Enums\PatientPreferredPhone;
use App\Events\NeedsWriteSystemComment;
use App\Events\PatientDocumentUpload;
use App\Exceptions\Officeally\OfficeallyAuthenticationException;
use App\Helpers\Constant\PatientDocsConst;
use App\Helpers\RetryJobQueueHelper;
use App\Helpers\Sites\OfficeAlly\Enums\AppointmentStatuses;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Http\Requests\Patient\Api\StorePatientAlertRequest;
use App\Models\Diagnose;
use App\Models\EligibilityPayer;
use App\Models\Language;
use App\Models\Patient\PatientTransfer;
use App\Option;
use App\Patient;
use App\PatientAssessmentForm;
use App\PatientComment;
use App\PatientInsurance;
use App\PatientInsurancePlan;
use App\PatientInsuranceProcedure;
use App\PatientStatus;
use App\Provider;
use App\Traits\Patient\PatientProvider;
use App\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Patient\Api\UpdateRequest;
use App\Http\Requests\Api\ReauthorizationRequestDashboard\UpdateAuthNumberRequest;
use App\Http\Requests\Patient\Api\UpdateSecondaryEmail as UpdateSecondaryEmailRequest;
use App\Http\Requests\Patient\Api\UpdatePatientVisitFrequency as UpdatePatientVisitFrequencyRequest;
use App\Models\PatientHasProvider;
use App\Jobs\Officeally\Retry\RetryCreatePatient;
use App\Jobs\Officeally\Retry\RetryDeleteUpcomingAppointments;
use App\Models\FaxModel\Fax;
use App\Models\GoogleMeeting;
use App\PatientDocument;
use App\PatientDocumentUploadInfo;
use App\PatientLeadDocument;
use App\Status;
use App\Models\Patient\PatientElectronicDocument;
use App\Models\RingcentralCallLog;
use App\PatientAlert;
use App\PatientDocumentType;
use App\PatientNote;
use App\Traits\GoogleDrive\DocumentTypes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use App\Jobs\Guzzle\PostPatientAlerts;
use App\Jobs\UploadPatientDocument;
use App\PatientVisitFrequency;
use App\PatientVisitFrequencyChange;

class PatientRepository implements PatientRepositoryInterface
{
    use PatientProvider, DocumentTypes;

    private const DEFAULT_PAGINATION = 20;

    /**
     * @param int $limit
     * @param string|null $searchQuery
     * @param User $user
     * @return LengthAwarePaginator
     */
    public function all(int $limit, $searchQuery, User $user): LengthAwarePaginator
    {
        $fullNameRaw = "CONCAT(patients.first_name, ' ', patients.last_name, ' ', patients.middle_initial)";

        $query = Patient::query()
            ->select([
                'patients.id as id',
                'patients.first_name as first_name',
                'patients.last_name as last_name',
                'patients.middle_initial as middle_initial',
                'patients.email',
                'patients.secondary_email',
                'patients.cell_phone',
                'patients.visit_frequency_id as visit_frequency', 
                DB::raw(
                    $fullNameRaw . ' AS full_name'
                ),
            ])
            ->where('id', '!=', 1111);
        if (!empty($searchQuery)) {
            $query->when($searchQuery, function ($query, $searchQuery) use ($fullNameRaw) {
                $query->whereRaw($fullNameRaw . ' like "%' . $searchQuery . '%" ');
            });
        }

        if ($user->isOnlyProvider()) {
            $query->whereHas('providers', function ($query) use ($user) {
                $query->where('providers.id', $user->provider_id);
            });
        }

        $pagination = empty($limit) ? self::DEFAULT_PAGINATION : $limit;

        return $query
            ->orderBy('full_name')
            ->paginate($pagination);
    }

    /**
     * @param array $data
     * @return Patient
     */
    public function create(array $data): Patient
    {
        $data['patient_id'] = null;
        $preparedData = $this->preparePatientData($data);
        $preparedData['status_id'] = PatientStatus::getNewId();
        $patient = Patient::create($preparedData);
        $patientId = $this->createInOfficeAlly($data, $patient->id);

        if ($patientId) {
            $patient->patient_id = (int)$patientId;
            $patient->save();
        }

        if (!empty($data['diagnoses'])) {
            $patient->diagnoses()->sync(array_pluck($data['diagnoses'], 'id'));
        }

        if (!empty($data['provider_id'])) {
            $provider = Provider::whereId($data['provider_id'])->first();
            $comment = trans('comments.provider_assigned_automatically', [
                'provider_name' => $provider->provider_name,
            ]);
            PatientHasProvider::create(['patients_id' => $patient->id, 'providers_id' => $data['provider_id']]);
            event(new NeedsWriteSystemComment($patient->id, $comment));
        } else {
            PatientTransfer::create([
                'patient_id' => $patient->id,
                'old_provider_id' => null,
                'created_by' => auth()->id(),
                'closed_at' => null,
                'unassigned_at' => null,
            ]);
        }

        $this->createTemplatesIfNeeded($data, $patient);
        $this->createDocumentsIfNeeded($data, $patient);
        $this->syncFaxesIfNeeded($data, $patient);

        return $patient;
    }

    /**
     * @param array $data
     * @param Patient $patient
     */
    protected function createTemplatesIfNeeded(array $data, Patient $patient): void
    {
        if (empty($data['templates'])) {
            return;
        }

        $patient->templates()
            ->each(function ($template) {
                $template->delete();
            });

        foreach ($data['templates'] as $key => $template) {
            if ($this->checkTemplateFields($template)) {
                $template['position'] = $key;
                $patient->templates()->create($template);
            }
        }
    }

    protected function createDocumentsIfNeeded(array $data, Patient $patient): void
    {
        if (empty($data['documents'])) {
            return;
        }

        foreach ($data['documents'] as $document) {
            $newDocument = $patient->documents()->create(array_except($document, ['id']));

            $doc = PatientLeadDocument::withoutAdminScope()
                ->where('id', $document['id'])
                ->first();

            if (isset($doc)) {
                $uploadInfo = [
                    'user_id' => $doc->uploadInfo->user_id,
                    'patient_document_id' => $newDocument->id,
                    'document_model' => 'App\PatientDocument',
                    'client_ip' => $doc->uploadInfo->client_ip,
                    'client_user_agent' => $doc->uploadInfo->client_user_agent,
                ];
                PatientDocumentUploadInfo::create($uploadInfo);
            }
        }
    }

    protected function syncFaxesIfNeeded(array $data, Patient $patient)
    {
        if (empty($data['faxes'])) {
            return;
        }

        Fax::whereIn('id', $data['faxes'])->update([
            'patient_lead_id' => null,
            'patient_id' => $patient->id,
        ]);
    }

    /**
     * @param array $data
     * @param int $patientId
     * @return int|null
     */
    protected function createInOfficeAlly(array $data, int $originalPatientId): int
    {
        $account = Option::OA_ACCOUNT_1;
        $officeAlly = new OfficeAllyHelper($account);

        $patientId = null;
        $preparedData = $this->prepareOAData($data);

        try {
            $patientId = $officeAlly->createPatient($preparedData);

            if ($patientId && array_key_exists('diagnoses', $preparedData) && $preparedData['diagnoses']) {
                $dataForUpdate = ['diagnoses' => $preparedData['diagnoses']];
                RetryJobQueueHelper::dispatchRetryUpdatePatient($account, $dataForUpdate, $originalPatientId);
            }
        } catch (OfficeallyAuthenticationException $e) {
            $delaySeconds = config('parser.job_retry_backoff_intervals')[0];
            $job = (new RetryCreatePatient($account, $preparedData, $originalPatientId))->delay(Carbon::now()->addSeconds($delaySeconds));
            dispatch($job);
        }

        return $patientId;
    }

    /**
     * @param array $data
     * @param bool $isUpdate
     * @return array
     */
    public function prepareOAData(array $data, bool $isUpdate = false): array
    {
        if (!empty($data)) {
            if (array_key_exists('date_of_birth', $data)) {
                $data['date_of_birth'] = !empty($data['date_of_birth'])
                    ? Carbon::parse($data['date_of_birth']) : null;
            }

            if (array_key_exists('insurance_id', $data)) {
                $data['insurance_id'] = !empty($data['insurance_id'])
                    ? PatientInsurance::whereId($data['insurance_id'])->first()->external_id : null;
            }

            if (array_key_exists('eligibility_payer_id', $data)) {
                $data['eligibility_payer_id'] = !empty($data['eligibility_payer_id'])
                    ? EligibilityPayer::whereId($data['eligibility_payer_id'])->first()->external_id : null;
            }

            if (array_key_exists('preferred_language_id', $data)) {
                $data['preferred_language_id'] = !empty($data['preferred_language_id'])
                    ? Language::whereId($data['preferred_language_id'])->first()->officeally_id : null;
            }

            if (array_key_exists('diagnoses', $data)) {
                if (empty($data['diagnoses'])) {
                    $data['diagnoses'] = [];
                } else {
                    $diagnoses = Diagnose::whereIn('id', array_pluck($data['diagnoses'], 'id'))->get();
                    $data['diagnoses'] = [];
                    foreach ($diagnoses as $diagnose) {
                        $data['diagnoses'][] = [
                            'code' => $diagnose->code,
                            'description' => $diagnose->description,
                        ];
                    }
                }
            }

            if (array_key_exists('templates', $data)) {
                if (empty($data['templates'])) {
                    $data['billable_lines'] = [];
                } else {
                    foreach ($data['templates'] as $key => $template) {
                        if ($this->checkTemplateFields($template)) {
                            $template['position'] = $key;
                            $data['cpt'] = !empty($data['patient_insurances_procedure_id'])
                                ? PatientInsuranceProcedure::whereId($data['patient_insurances_procedure_id'])->first()->code : null;
                            $data['billable_lines'][] = $template;
                        }
                    }
                }
            }

            $phoneKeys = ['cell_phone', 'work_phone', 'home_phone'];
            foreach ($phoneKeys as $phoneKey) {
                if (array_key_exists($phoneKey, $data)) {
                    $data[$phoneKey] = split_phone($data[$phoneKey]);
                }
            }

            if (array_key_exists('provider_id', $data)) {
                if (empty($data['provider_id'])) {
                    $data['primary_care_provider'] = null;
                } else {
                    $data['primary_care_provider'] = Provider::withTrashed()->whereKey($data['provider_id'])->first()->officeally_id;
                }
            }

            if (array_key_exists('subscriber_id', $data)) {
                $data['mrn'] = $data['subscriber_id'];
            }

            if (array_key_exists('eff_start_date', $data)) {
                $data['eff_start_date'] = !empty($data['eff_start_date'])
                    ? Carbon::parse($data['eff_start_date']) : null;
            }

            if (array_key_exists('eff_stop_date', $data)) {
                $data['eff_stop_date'] = !empty($data['eff_stop_date'])
                    ? Carbon::parse($data['eff_stop_date']) : null;
            }
        }

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function preparePatientData(array $data): array
    {
        $preferredPhone = null;
        if (isset($data['preferred_phone'])) {
            if (isset(PatientPreferredPhone::$list[$data['preferred_phone']])) {
                $preferredPhone = $data['preferred_phone'];
            } else if (array_search($data['preferred_phone'], PatientPreferredPhone::$list)) {
                $preferredPhone = array_search($data['preferred_phone'], PatientPreferredPhone::$list);
            }
        }

        $planNameId = !empty($data['plan_name']) && !empty($data['insurance_id'])
            ? PatientInsurancePlan::firstOrCreate([
                'insurance_id' => $data['insurance_id'],
                'name' => $data['plan_name'],
            ])->id : null;

        return [
            'patient_id' => $data['patient_id'],
            'first_name' => $data['first_name'],
            'middle_initial' => $data['middle_initial'] ?? '',
            'last_name' => $data['last_name'],
            'sex' => $data['sex'] ?? null,
            'preferred_language_id' => $data['preferred_language_id'] ?? null,
            'email' => $data['email'] ?? null,
            'secondary_email' => $data['secondary_email'] ?? null,
            'address' => $data['address'] ?? null,
            'address_2' => $data['address_2'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'zip' => $data['zip'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'cell_phone' => $data['cell_phone'] ?? null,
            'home_phone' => $data['home_phone'] ?? null,
            'work_phone' => $data['work_phone'] ?? null,
            'preferred_phone' => $preferredPhone,
            'primary_insurance_id' => $data['insurance_id'] ?? null,
            'primary_insurance' => optional(PatientInsurance::find($data['insurance_id'] ?? null))->insurance,
            'auth_number' => $data['auth_number'] ?? null,
            'visits_auth' => $data['visits_auth'] ?? null,
            'visits_auth_left' => $data['visits_auth_left'] ?? null,
            'eff_start_date' => $data['eff_start_date'] ?? null,
            'eff_stop_date' => $data['eff_stop_date'] ?? null,
            'subscriber_id' => $data['subscriber_id'] ?? null,
            'insurance_plan_id' => $planNameId,
            'is_self_pay' => $data['is_self_pay'] ?? null,
            'self_pay' => $data['self_pay'] ?? null,
            'visit_copay' => $data['visit_copay'] ?? null,
            'deductible' => $data['deductible'] ?? null,
            'deductible_met' => $data['deductible_met'] ?? null,
            'deductible_remaining' => $data['deductible_remaining'] ?? null,
            'insurance_pay' => $data['insurance_pay'] ?? null,
            'therapy_type_id' => $data['therapy_type_id'] ?? null,
            'eligibility_payer_id' => $data['eligibility_payer_id'] ?? null,
            'primary_provider_id' => $data['provider_id'] ?? null,
            'is_payment_forbidden' => $data['is_payment_forbidden'] ?? false
        ];
    }

    /**
     * @param Patient $patient
     * @return array
     */
    public function show(Patient $patient): array
    {
        $patient = $patient->load([
            'diagnoses',
            'insurance',
            'insurancePlan',
            'eligibilityPayer',
            'templates',
            'primaryProvider' => function ($query) {
                $query->withTrashed();
            },
            'preferredLanguage',
        ]);

        $data = array_only($patient->toArray(), [
            'id',
            'patient_id',
            'auth_number',
            'first_name',
            'last_name',
            'middle_initial',
            'date_of_birth',
            'sex',
            'email',
            'secondary_email',
            'cell_phone',
            'home_phone',
            'work_phone',
            'preferred_phone',
            'visits_auth',
            'visits_auth_left',
            'address',
            'address_2',
            'city',
            'state',
            'zip',
            'subscriber_id',
            'eff_start_date',
            'eff_stop_date',
            'is_self_pay',
            'self_pay',
            'visit_copay',
            'deductible',
            'deductible_met',
            'deductible_remaining',
            'insurance_pay',
            'therapy_type_id',
            'preferred_language_id',
            'is_payment_forbidden',

            'eligibility_payer',    //Health Plan Elig. Benefit Co. ID
            'diagnoses',
            'insurance',
            'insurance_plan',
            'templates',
            'primary_provider',
            'preferred_language',
        ]);

        $data['preferred_phone'] = PatientPreferredPhone::$list[$data['preferred_phone']] ?? null;

        return $data;
    }

    /**
     * @param array $data
     * @param Patient $patient
     * @return Patient
     */
    public function update(array $data, Patient $patient): Patient
    {
        $this->updateInOfficeAlly($patient, $data);
        $data['patient_id'] = $patient->patient_id;
        $patient->update($this->preparePatientData($data));

        if (array_key_exists('diagnoses', $data)) {
            $stats = $patient->diagnoses()->sync(array_pluck($data['diagnoses'], 'id'));
            if (count($stats['attached']) > 0 || count($stats['detached']) > 0) {
                $userName = auth()->user()->provider ? auth()->user()->provider->provider_name : auth()->user()->meta->firstname . ' ' . auth()->user()->meta->lastname;
                event(new NeedsWriteSystemComment($patient->id, trans('comments.diagnose_changed_by_provider', ['provider_name' => $userName])));
            }
        }

        if (!empty($data['provider_id'])) {
            $this->connectProvider(Provider::withTrashed()->whereId($data['provider_id'])->first(), $patient);
        }

        $this->createTemplatesIfNeeded($data, $patient);

        return $patient;
    }

    /**
     * @param array $data
     * @param Patient $patient
     * @return Patient
     */
    public function updateAttachedProviders(array $data, Patient $patient): Patient
    {
        $providers = array_pluck($data['providers'], null, 'id');
        $providers = array_map(function ($provider) {
            return ['chart_read_only' => $provider['read_only']];
        }, $providers);

        $stats = $this->syncPatientProviders($patient, $providers);

        $commentData = [];
        if (!empty($stats['attached'])) {
            $adminName = auth()->user()->meta->firstname . ' ' . auth()->user()->meta->lastname;
            $providers = Provider::query()->withTrashed()->whereIn('id', $stats['attached'])->pluck('provider_name', 'id')->toArray();
            foreach ($stats['attached'] as $providerId) {
                $commentData[] = [
                    'comment'    => trans('comments.admin_assigned_provider', [
                        'admin_name'    => $adminName,
                        'provider_name' => $providers[$providerId],
                    ]),
                    'patient_id' => $patient->id,
                ];
            }
        }
        if (!empty($stats['detached'])) {
            $adminName = auth()->user()->meta->firstname . ' ' . auth()->user()->meta->lastname;
            $providers = Provider::query()->withTrashed()->whereIn('id', $stats['detached'])->get()->pluck(null, 'id')->toArray();
            if ($patient->primary_provider_id && in_array($patient->primary_provider_id, $stats['detached'])) {
                $account = Option::OA_ACCOUNT_1;
                $officeAllyHelper = new OfficeAllyHelper($account);

                $delaySeconds = config('parser.job_retry_backoff_intervals')[0];

                if ($patient->patient_id) {
                    try {
                        $officeAllyHelper->deleteUpcomingAppointments($patient->patient_id, $providers[$patient->primary_provider_id]['officeally_id']);
                    } catch (OfficeallyAuthenticationException $e) {
                        $job = (new RetryDeleteUpcomingAppointments($patient->id, $account, $providers[$patient->primary_provider_id]['officeally_id']))->delay(Carbon::now()->addSeconds($delaySeconds));
                        dispatch($job);
                    }
                } else {
                    $job = (new RetryDeleteUpcomingAppointments($patient->id, $account, $providers[$patient->primary_provider_id]['officeally_id']))->delay(Carbon::now()->addSeconds($delaySeconds));
                    dispatch($job);
                }
                /**
                 * unassign primary care provider
                 */
                $dataForUpdate = [
                    'new_primary_care_provider' => null,
                    'delete_primary_care_provider' => $providers[$patient->primary_provider_id]['officeally_id'],
                ];

                RetryJobQueueHelper::dispatchRetryUpdatePatient($account, $dataForUpdate, $patient->id);

                $patient->primary_provider_id = null;
                $patient->save();
            }

            foreach ($stats['detached'] as $providerId) {
                $commentData[] = [
                    'comment'    => trans('comments.admin_unassigned_provider', [
                        'admin_name'    => $adminName,
                        'provider_name' => $providers[$providerId]['provider_name'],
                    ]),
                    'patient_id' => $patient->id,
                ];
            }
        }
        if (!empty($commentData)) {
            PatientComment::bulkAddComments($commentData, true);
        }

        return $patient;
    }

    /**
     * @param Patient $patient
     * @param array $data
     */
    protected function updateInOfficeAlly(Patient $patient, array $data)
    {
        $preparedData = $this->prepareOAData($data);

        RetryJobQueueHelper::dispatchRetryUpdatePatient(Option::OA_ACCOUNT_1, $preparedData, $patient->id);
    }

    /**
     * @param array $template
     * @return bool
     */
    private function checkTemplateFields(array $template)
    {
        return !empty($template['pos']) || !empty($template['patient_insurances_procedure_id']) || !empty($template['modifier_a']) || !empty($template['modifier_b'])
            || !empty($template['modifier_c']) || !empty($template['modifier_d']) || !empty($template['charge']) || !empty($template['days_or_units']);
    }

    public function updateAuthNumber(UpdateAuthNumberRequest $request)
    {
        $inputData = $request->validated();

        $patient = Patient::find($inputData['patient_id']);
        $dataToUpdate = [
            'auth_number' => $inputData['auth_number'],
            'visits_auth' => $inputData['visits_auth'],
            'visits_auth_left' => $inputData['visits_auth'],
            'eff_start_date' => $inputData['eff_start_date'],
            'eff_stop_date' => $inputData['eff_stop_date'],
        ];

        $this->updateInOfficeAlly($patient, $dataToUpdate);
        $patient->update($dataToUpdate);
        
        return $patient;
    }

    public function updatePatientLanguagePrefer(UpdateRequest $request)
    {
        $inputData = $request->toArray();

        $patient = Patient::find($inputData['patient_id']);
        $language = Language::where('title', $inputData['language_prefer'])->first();

        if ($patient->preferred_language_id !== $language->id) {
            $dataToUpdate = [
                'preferred_language_id' => $language->id,
            ];

            $this->updateInOfficeAlly($patient, $dataToUpdate);
            $patient->update($dataToUpdate);
        }

        return $patient;
    }

    public function addPatientSecondaryEmail(UpdateSecondaryEmailRequest $request)
    {
        $inputData = $request->toArray();
        $patientId = $inputData['patient_id'];
        return Patient::find($patientId)->update([
            'secondary_email' => $inputData['secondary_email']
        ]);
    }

    public function updatePatientVisitFrequency(UpdatePatientVisitFrequencyRequest $request)
    {
        $inputData = $request->toArray();
        $patientId = $inputData['patient_id'];
        $patient = Patient::find($patientId);
        $oldVisitFrequencyId = $patient->visit_frequency_id;
        $newVisitFrequencyId = $inputData['visit_frequency_id'];
        $user = Auth::user();

        if ($oldVisitFrequencyId !== $newVisitFrequencyId) {
            $patient->update([
                'visit_frequency_id' => $newVisitFrequencyId
            ]);
    
            PatientVisitFrequencyChange::create([
                'patient_id' => $patientId,
                'old_visit_frequency_id' => $oldVisitFrequencyId,
                'new_visit_frequency_id' => $newVisitFrequencyId,
                'changed_by' => $user->id,
                'comment' => isset($inputData['comment']) ? e($inputData['comment']) : null,
            ]);

            $commentData = [
                'patient_id' => $patientId,
                'admin_id' => $user->isAdmin() ? $user->id : null,
                'provider_id' => $user->isProvider() ? $user->provider_id : null,
                'comment' => isset($inputData['comment']) ? e($inputData['comment']) : null,
                'comment_type' => PatientComment::CHANGE_VISIT_FREQUENCY_TYPE,
                'metadata' => [
                    'old_value' => $oldVisitFrequencyId ? PatientVisitFrequency::getNameById($oldVisitFrequencyId) : null,
                    'new_value' => PatientVisitFrequency::getNameById($newVisitFrequencyId)
                ]
            ];
            PatientComment::create($commentData);
        }
    }

    private function syncPatientProviders(Patient $patient, array $providers): array
    {
        $stats = ["attached" => [], "detached" => [], "updated" => []];

        $patientProviders = $patient->allProviders()->withTrashed()->withPivot('chart_read_only')->get();

        foreach ($providers as $provider) {
            $patientProvider = $patientProviders->where('id', $provider['id'])->first();

            if ($patientProvider) {
                if ($patientProvider->pivot->chart_read_only !== $provider['chart_read_only']) {
                    $patientHasProvider = PatientHasProvider::where('patients_id', $patient->id)
                        ->where('providers_id', $provider['id'])
                        ->first();

                    if ($patientHasProvider) {
                        $patientHasProvider->update(['chart_read_only' => $provider['chart_read_only']]);
                        $stats['updated'][] = $provider['id'];
                    }
                }
            } else {
                PatientHasProvider::create([
                    'patients_id' => $patient->id,
                    'providers_id' => $provider['id'],
                    'chart_read_only' => $provider['chart_read_only']
                ]);
                $stats['attached'][] = $provider['id'];
            }
        }

        $providerIds = collect($providers)->pluck('id')->toArray();
        $providersToDelete = $patientProviders->whereNotIn('id', $providerIds);

        foreach ($providersToDelete as $provider) {
            $patientHasProvider = PatientHasProvider::where('patients_id', $patient->id)->where('providers_id', $provider->id)->first();

            if ($patientHasProvider) {
                $patientHasProvider->delete();
                $stats['detached'][] = $provider['id'];
            }
        }

        return $stats;
    }

    public function addPatientAlertData(array $alertData): PatientAlert
    {
        $patientId = $alertData['patient_id'];

        if (isset($alertData['file'])) {
            $file = $alertData['file'];
            $documentOptions = [
                'patient_id' => $patientId,
                'document_type_id' => PatientDocumentType::getEligibilityVerificationId(),
                'only_for_admin' => true,
                'visible' => true,
            ];
            $patientDocument = \Bus::dispatchNow(new UploadPatientDocument($file, $documentOptions));
        }

        $patientAlert = PatientAlert::create([
            'patient_id' => $patientId,
            'date_created' => Carbon::now()->format('Y-m-d'),
            'status' => 0,
            'message' => $alertData['message'],
            'co_pay' => $alertData['co_pay'],
            'deductible' => $alertData['deductible'],
            'deductible_met' => $alertData['deductible_met'],
            'deductible_remaining' => $alertData['deductible_remaining'],
            'insurance_pay' => $alertData['insurance_pay'],
            'reference_number' => $alertData['reference_number'] ?? '',
            'recorded_by' => auth()->id(),
            'patient_document_id' => isset($patientDocument) ? $patientDocument->id : null,
        ]);

        $patient = Patient::find($patientId);
        $patient->update([
            'visit_copay' => $alertData['co_pay'],
            'deductible' => $alertData['deductible'],
            'deductible_met' => $alertData['deductible_met'],
            'deductible_remaining' => $alertData['deductible_remaining'],
            'insurance_pay' => $alertData['insurance_pay'],
        ]);
        $dataToUpdate = [
            'visit_copay' => $alertData['co_pay'],
            'deductible' => $alertData['deductible'],
        ];
        $this->updateInOfficeAlly($patient, $dataToUpdate);

        \Bus::dispatchNow(new PostPatientAlerts($alertData));

        $patientAlert->recorded_by_name = auth()->user()->getFullname();

        return $patientAlert;
    }

    public function getPatientsWithoutUpcomingAppointments(array $filters = []): Collection
    {
        $visitCreatedId = Status::getVisitCreatedId();

        $patientsQuery = Patient::query()
            ->select(
                'patients.id',
                'patients.primary_insurance',
                'patients.secondary_insurance',
                'patients.first_name',
                'patients.last_name',
                'patients.status_id',
                DB::raw('DATEDIFF(CURRENT_TIMESTAMP, FROM_UNIXTIME(MAX(appointments.time))) AS last_appointment_date'),
                DB::raw('COUNT(appointments.id) AS appointment_count'),
                'providers.provider_name',
                'patient_statuses.status',
                'patient_statuses.hex_color AS status_color',
                'patients.visits_auth'
            )->selectSub(function ($query) {
                $query->selectRaw('COUNT(appointments.id)')
                ->from('appointments')
                ->whereColumn('patients_id', 'patients.id')
                ->whereNull('deleted_at')
                ->where('time', '>', Carbon::now()->timestamp);
            }, 'upcoming_appointments')
            ->join('appointments', 'patients.id', '=', 'appointments.patients_id')
            ->join('patients_has_providers', 'patients_has_providers.patients_id', '=', 'patients.id')
            ->join('providers', 'providers.id', '=', 'patients_has_providers.providers_id')
            ->join('patient_statuses', 'patient_statuses.id', '=', 'patients.status_id')
            ->where('patients.watching', 1)
            ->whereNull('providers.deleted_at')
            ->where('patients_has_providers.chart_read_only', 0)
            ->whereNull('appointments.deleted_at')
            ->where('appointments.appointment_statuses_id', $visitCreatedId);

        if (isset($filters['provider_id'])) {
            $patientsQuery->where('providers.id', $filters['provider_id']);
        }

        if (isset($filters['patient_statuses'])) {
            $patientsQuery->whereIn('patients.status_id', $filters['patient_statuses']);
        }

        $patientsQuery->groupBy('patients.id')
            ->having('last_appointment_date', '>', 0)
            ->having('upcoming_appointments', 0)
            ->orderBy('patients.first_name', 'ASC')
            ->orderBy('patients.last_name', 'ASC');

        return $patientsQuery->get();
    }

    /**
     * Get count of different patient documents type.
     * @return array
     */
    public function patientNotesWithDocumentsCount($patientId): array
    {
        //count all patient docs
        $patientElectronicDocument = PatientElectronicDocument::select('id')
            ->where('patient_id', $patientId)
            ->whereNotIn('document_type_id', AssessmentForm::getFileTypeIDsLikeInitialAssessment())
            ->count();

        $patientDocument = PatientDocument::select('id')
            ->where('patient_id', $patientId)
            ->where('visible', 1)
            ->whereNotIn('document_type_id', PatientDocumentType::getFileTypeIDsLikeInitialAssessment())
            ->when(!Auth::user()->isAdmin(), function ($query) {
                return $query->where('only_for_admin', 0);
            })
            ->count();

        $documentsCount =  $patientElectronicDocument + $patientDocument;

        //count all patient private docs
        $privateDocumentsCount = '0';
        if (Auth::user()->isAdmin()) {
            $privateDocumentsCount = PatientDocument::select('id')
            ->where('patient_id', $patientId)
            ->where('only_for_admin', 1)
            ->count();
        }
       
        //count all initial assessments docs
        $initialAssessmentsPatientElectronicDocument = PatientElectronicDocument::select('id')
            ->where('patient_id', $patientId)
            ->whereIn('document_type_id', AssessmentForm::getFileTypeIDsLikeInitialAssessment())
            ->count();

        $initialAssessmentsPatientDocument = PatientDocument::select('id')
            ->where('patient_id', $patientId)
            ->where('visible', 1)
            ->whereIn('document_type_id', PatientDocumentType::getFileTypeIDsLikeInitialAssessment())
            ->when(!Auth::user()->isAdmin(), function ($query) {
                return $query->where('only_for_admin', 0);
            })
            ->count();

        $initialAssessmentsCount = $initialAssessmentsPatientElectronicDocument + $initialAssessmentsPatientDocument;

        //count all telehealth sessions
        $telehealthSessionsCount = GoogleMeeting::select('id')
            ->join('google_meeting_call_logs', 'google_meeting_call_logs.google_meeting_id', '=', 'google_meetings.id')
            ->where(['google_meeting_call_logs.is_initial' => 1, 'google_meetings.patient_id' => $patientId])
            ->count();

        //count all patient ringcentral call log
        $callLogsCount =  RingcentralCallLog::select('id')
            ->where('patient_id', $patientId)
            ->when(!Auth::user()->isAdmin(), function ($query) {
                return $query->where('only_for_admin', 0);
            })
            ->count();

        //count all patient comments
        $commentsCount =  PatientComment::select('id')
        ->where(['patient_id' => $patientId, 'is_system_comment' =>  0, 'only_for_admin' => 0])
        ->count();

        //count all patient private docs
        $privateCommentsCount = '0';
        if (Auth::user()->isAdmin()) {
            $privateCommentsCount = PatientComment::select('id')
            ->where(['patient_id' => $patientId, 'is_system_comment' =>  0, 'only_for_admin' => 1])
            ->count();
        }

        //count all patient alert comments
        $alertsCount =  PatientComment::select('id')->where(['patient_id' => $patientId, 'is_system_comment' =>  1])->count();

        //count patient progress notes
        $progressNotesCount  = PatientNote::select('id')->where('patients_id', $patientId)->count();

        //count all patient data in chart
        $allPatientDataCount = (int)$documentsCount + (int)$telehealthSessionsCount
            + (int)$commentsCount + (int)$privateCommentsCount + (int)$privateDocumentsCount
            + (int)$alertsCount + (int)$progressNotesCount
            + (int)$initialAssessmentsCount + (int)$callLogsCount;

        return [
            'documents_count' => $documentsCount,
            'private_docs_count' => $privateDocumentsCount,
            'initial_assessments_count' => $initialAssessmentsCount,
            'telehealth_sessions_count' => $telehealthSessionsCount,
            'call_logs_count' => $callLogsCount,
            'comments_count' => $commentsCount,
            'private_comments_count' => $privateCommentsCount,
            'alerts_count' => $alertsCount,
            'progress_notes_count' => $progressNotesCount,
            'all_patient_data_count' => $allPatientDataCount,
        ];
    }

    public function checkIsSynchronized(Patient $patient): bool
    {
        return !$patient->start_synchronization_time;
    }
}

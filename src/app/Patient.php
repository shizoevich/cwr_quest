<?php

namespace App;

use Illuminate\Support\Facades\DB;
use App\Status;
use App\Models\Diagnose;
use App\Models\EligibilityPayer;
use App\Models\FaxModel\Fax;
use App\Models\FaxModel\FaxComment;
use App\Models\FaxModel\FaxStatus;
use App\Models\Language;
use App\Models\Officeally\OfficeallyTransaction;
use App\Models\Patient\Lead\PatientLead;
use App\Models\Patient\PatientAdditionalPhone;
use App\Models\Patient\PatientTag;
use App\Models\Patient\PatientTransfer;
use App\Models\PatientHasProvider;
use App\PatientDocument;
use App\Models\Patient\DocumentRequest\PatientDocumentRequest;
use App\Models\Patient\DocumentRequest\PatientFormType;
use App\Models\Patient\Inquiry\PatientInquiry;
use App\Models\Patient\PatientElectronicDocument;
use App\Models\Patient\PatientForm;
use App\Models\Patient\PatientPreprocessedTransaction;
use App\Models\Patient\PatientRemovalRequest;
use App\Models\Patient\PatientTemplate;
use App\Models\Patient\PatientTransaction;
use App\Models\Patient\PatientTransactionAdjustment;
use App\Models\PatientTherapyType;
use App\Models\TridiuumPatient;
use App\Models\RingcentralCallLog;
use App\Models\SubmittedReauthorizationRequestForm;
use App\PatientVisitFrequency;
use App\Scopes\PatientDocuments\DocumentsForAllScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

/**
 * App\Patient
 *
 * @property int $id
 * @property int|null $patient_id
 * @property string|null $patient_account_number
 * @property float|null $auth_number
 * @property string|null $first_name
 * @property string|null $home_phone
 * @property bool|null $parse_home_phone
 * @property string|null $last_name
 * @property string|null $middle_initial
 * @property string|null $email
 * @property string|null $secondary_email
 * @property string|null $insured_name
 * @property string|null $secondary_insured_name
 * @property string|null $address
 * @property string|null $address_2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property string|null $date_of_birth
 * @property string|null $age
 * @property string|null $cell_phone
 * @property bool|null $parse_cell_phone
 * @property string|null $work_phone
 * @property bool|null $parse_work_phone
 * @property int|null $preferred_phone
 * @property int|null $visits_auth
 * @property int|null $visits_auth_left
 * @property string|null $primary_insurance
 * @property int|null $primary_insurance_id
 * @property int|null $insurance_plan_id
 * @property string|null $secondary_insurance
 * @property string|null $sex
 * @property string|null $elig_copay
 * @property string|null $elig_status
 * @property string|null $reffering_provider
 * @property bool|null $is_payment_forbidden
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property int $completed_appointment_count
 * @property int $watching
 * @property int|null $visit_copay
 * @property int $hidden_on_patients_without_appointments_statistics
 * @property string|null $created_patient_date
 * @property int|null $status_id
 * @property string|null $eff_stop_date
 * @property string|null                                                                                  $eff_start_date
 * @property string|null                                                                                  $subscriber_id
 * @property bool|null $is_parsed_cancellation_fee
 * @property int                                                                                          $charge_for_cancellation_appointment
 * @property bool|null $tmp_is_manually_archived
 * @property \Carbon\Carbon|null                                                                          $start_synchronization_time
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientAlert[]                            $alerts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Provider[]                                $allProviders
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Appointment[]                             $appointments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientAssessmentForm[]                   $assessmentForms
 * @property-read \App\Models\Patient\PatientTransaction                                                  $balance
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientComment[]                          $comments
 * @property-read \App\PatientDiagnoseOld                                                                 $diagnose
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientDocument[]                         $documents
 * @property-read \App\PatientInformationForm                                                             $informationForm
 * @property-read \App\PatientInsurance|null                                                              $insurance
 * @property-read \App\PatientInsurancePlan|null                                                          $insurancePlan
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Officeally\OfficeallyTransaction[] $officeallyTransactions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientFormFirst[]                        $patientFormFirsts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientNote[]                             $patientNotes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Patient\PatientRemovalRequest[]    $removeRequests
 * @property-read \App\PatientSquareAccount                                                               $squareAccount
 * @property-read \App\PatientStatus|null
 * @property-read TwilioSubscribe twilioSubscribe                                                              $status
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Patient\PatientTransaction[] $transactions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientVisit[] $visits
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient archived()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient notArchived()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient notDischarged()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient statusActive()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient statusInactive()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient statusLost()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient statusNew()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient statusNotNew()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereAuthNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereCellPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereChargeForCancellationAppointment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereCompletedAppointmentCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereCreatedPatientDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereEffStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereEffStopDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereEligCopay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereEligStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereHiddenOnPatientsWithoutAppointmentsStatistics($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereHomePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereInsurancePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereInsuredName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereMiddleInitial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient wherePatientAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient wherePrimaryInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient wherePrimaryInsuranceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereRefferingProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereSecondaryInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereSecondaryInsuredName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereStartSynchronizationTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereSubscriberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereVisitCopay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereVisitsAuth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereVisitsAuthLeft($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereWatching($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereWorkPhone($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientSquareAccount[] $squareAccounts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Patient\PatientTransactionAdjustment[] $transactionAdjustments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Patient\PatientRemovalRequest[] $removalRequests
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Patient\PatientElectronicDocument[] $electronicDocuments
 * @property-read \App\Models\Patient\PatientPreprocessedTransaction $preprocessedBalance
 */
class Patient extends Model implements LoggableModelInterface
{
    use Notifiable;

    protected $table = 'patients';

    protected $casts = [
        'is_payment_forbidden' => 'boolean',
        'is_self_pay' => 'boolean'
    ];

    /**
     * @var array
     */
    protected $guarded = [];

    protected $dates = ['start_synchronization_time'];

    private const LOG_FORBIDDEN_FIELDS = [
        'updated_at',
    ];

    public function getFullName()
    {
        return implode(' ', [$this->first_name, $this->last_name]);
    }

    public function canChargeLateCancellationFee()
    {
        $charge_for_cancellation = $this->charge_for_cancellation_appointment ?? 0;

        return [
            'booking_cancellation_policy' => [
                'is_completed_form' => $this->isCompletedIntakeForms(),
                'is_card_on_file' => $this->hasCreditCard(),
                'is_charge_for_cancellation_non_zero' => $charge_for_cancellation > 0,
                'is_supported_by_insurance' => !$this->is_payment_forbidden,
            ],
            'charge_for_cancellation' => $charge_for_cancellation
        ];
    }

    public function isCompletedIntakeForms(): bool
    {
        $newPatientDocumentTypeId = PatientDocumentType::getNewPatientId();

        $hasNewPatientDocumentBeforePaymentForServiceChanged = PatientDocument::query()
            ->where('document_type_id', $newPatientDocumentTypeId)
            ->where('patient_id', $this->id)
            ->where('created_at', '<', PatientDocument::getPaymentForServiceChangeDate())
            ->exists();

        if ($hasNewPatientDocumentBeforePaymentForServiceChanged) {
            return true;
        }

        $paymentForServiceDocumentTypeId = PatientDocumentType::getPaymentForServiceId();

        $hasNewPatientDocument = PatientDocument::query()
            ->where('document_type_id', $newPatientDocumentTypeId)
            ->where('patient_id', $this->id)
            ->exists();
        $hasPaymentForServiceDocument = PatientDocument::query()
            ->where('document_type_id', $paymentForServiceDocumentTypeId)
            ->where('patient_id', $this->id)
            ->exists();

        return $hasNewPatientDocument && $hasPaymentForServiceDocument;
    }

    private function isAttendancePolicySigned(): bool
    {
        return $this->formRequests()
            ->whereHas('items', function ($query) {
                $query->where('form_type_id', PatientFormType::getIdByName('attendance_policy'))
                    ->whereNotNull('filled_at');
            })
            ->exists();
    }

    public function getLateCancellationAmount()
    {
        if ($this->is_payment_forbidden || !$this->hasCreditCard()) {
            return 0;
        }

        return $this->charge_for_cancellation_appointment ?? 0;
    }

    public function appointments()
    {
        return $this->hasMany('App\Appointment', 'patients_id', 'id');
    }

    public function appointmentsTimeDesc()
    {
        return $this->appointments()->orderBy('time', 'desc');
    }

    public function lastAppointment()
    {
        return $this->hasOne(Appointment::class, 'patients_id')->latest('time');
    }

    public function lastCompletedVisitCreatedAppointment()
    {
        $appointmentStatuses = Status::getCompletedVisitCreatedStatusesId();

        return $this->hasOne(Appointment::class, 'patients_id')
            ->whereIn('appointment_statuses_id', $appointmentStatuses)
            ->latest('time');
    }

    public function allProviders()
    {
        return $this->belongsToMany('App\Provider', 'patients_has_providers', 'patients_id', 'providers_id');
    }

    public function providers()
    {
        return $this->allProviders()->where('chart_read_only', false);
    }

    public function primaryProvider()
    {
        return $this->belongsTo(Provider::class, 'primary_provider_id');
    }

    public function readOnlyProviders()
    {
        return $this->allProviders()->where('chart_read_only', true);
    }

    public function patientNotes()
    {
        return $this->hasMany('App\PatientNote', 'patients_id', 'id');
    }

    public function patientFormFirsts()
    {
        return $this->hasMany('App\PatientFormFirst', 'patients_id', 'id');
    }

    public function documents()
    {
        $response = $this->hasMany('App\PatientDocument');
        if (!is_null(Auth::user()) && Auth::user()->isAdmin()) {
            $response->withoutGlobalScope(DocumentsForAllScope::class);
        }
        return $response;
    }

    public function lastDocumentConsentInfo()
    {
        return PatientDocument::query()
            ->select('patient_document_consent_info.*')
            ->join('patient_document_consent_info', 'patient_document_consent_info.patient_document_id', '=', 'patient_documents.id')
            ->where('patient_documents.patient_id', '=', $this->id)
            ->where('patient_documents.document_type_id', PatientDocumentType::getNewPatientId())
            ->where(function ($query) {
                $query->whereNotNull('patient_document_consent_info.allow_home_phone_call')
                    ->orWhereNotNull('patient_document_consent_info.allow_mobile_phone_call')
                    ->orWhereNotNull('patient_document_consent_info.allow_work_phone_call');
            })
            ->orderBy('patient_documents.created_at', 'desc')
            ->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function electronicDocuments()
    {
        return $this->hasMany(PatientElectronicDocument::class);
    }

    public function comments()
    {
        return $this->hasMany('\App\PatientComment');
    }

    public function comment()
    {
        return $this->hasOne('\App\PatientComment');
    }

    public function status()
    {
        return $this->belongsTo('\App\PatientStatus', 'status_id');
    }

    public function diagnose()
    {
        return $this->hasOne('App\PatientDiagnoseOld', 'patient_officeally_id', 'patient_id');
    }

    public function diagnoses()
    {
        return $this->belongsToMany(Diagnose::class, 'patient_diagnoses');
    }

    public function assessmentForms()
    {
        return $this->hasMany(PatientAssessmentForm::class, 'patient_id', 'id');
    }

    public static function getPnCoefficient($id)
    {
        return Appointment::where('patients_id', $id)
            ->onlyVisitCreated()
            ->where('note_on_paper', '=', true)
            ->count();
        //	    return Patient::select('completed_appointment_count')
        //            ->where('id', $id)
        //            ->firstOrFail()
        //            ->toArray()['completed_appointment_count'];

    }

    public function alerts()
    {
        return $this->hasMany('App\PatientAlert');
    }

    public function alert()
    {
        return $this->hasOne('App\PatientAlert', 'patient_id', 'id')
            ->orderBy('date_created', 'desc');
    }

    public function visits()
    {
        return $this->hasMany('\App\PatientVisit');
    }
    public function informationForm()
    {
        return $this->hasOne(PatientInformationForm::class);
    }


    public function squareAccount()
    {
        return $this->hasOne(PatientSquareAccount::class)
            ->orderBy('created_at', 'desc');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function squareAccounts()
    {
        return $this->hasMany(PatientSquareAccount::class);
    }

    public function insurance()
    {
        return $this->belongsTo(PatientInsurance::class, 'primary_insurance_id', 'id');
    }

    public function insurancePlan()
    {
        return $this->belongsTo(PatientInsurancePlan::class, 'insurance_plan_id', 'id');
    }

    public function insuranceReauthorizationRequestForms()
    {
        return $this->hasMany(SubmittedReauthorizationRequestForm::class, 'patient_id');
    }

    public function scopeArchived($query)
    {
        $archivedId = PatientStatus::getArchivedId();

        return $query->where('status_id', $archivedId);
    }

    public function scopeNotArchived($query)
    {
        $archivedId = PatientStatus::getArchivedId();

        return $query->where('status_id', '!=', $archivedId);
    }

    public function scopeNotDischarged($query)
    {
        $dischargedId = PatientStatus::getDischargedId();

        return $query->where('status_id', '!=', $dischargedId);
    }

    public function scopeStatusActive($query)
    {
        $statusId = PatientStatus::getActiveId();

        return $query->where('status_id', $statusId);
    }

    public function scopeStatusInactive($query)
    {
        $statusId = PatientStatus::getInactiveId();

        return $query->where('status_id', $statusId);
    }

    public function scopeStatusLost($query)
    {
        $statusId = PatientStatus::getLostId();

        return $query->where('status_id', $statusId);
    }

    public function scopeStatusNew($query)
    {
        $statusId = PatientStatus::getNewId();

        return $query->where('status_id', $statusId);
    }

    public function scopeStatusNotNew($query)
    {
        $statusId = PatientStatus::getNewId();

        return $query->where('status_id', '!=', $statusId);
    }

    /**
     * @return $this
     */
    public function balance()
    {
        return $this->hasOne(PatientTransaction::class, 'patient_id', 'id')
            ->orderBy('id', 'desc');
    }

    /**
     * @return $this
     */
    public function preprocessedBalance()
    {
        return $this->hasOne(PatientPreprocessedTransaction::class, 'patient_id', 'id')
            ->orderBy('id', 'desc');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(PatientTransaction::class, 'patient_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactionAdjustments()
    {
        return $this->hasMany(PatientTransactionAdjustment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function officeallyTransactions()
    {
        return $this->hasMany(OfficeallyTransaction::class, 'patient_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function removalRequests()
    {
        return $this->hasMany(PatientRemovalRequest::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function inquiries(): MorphMany
    {
        return $this->morphMany(PatientInquiry::class, 'inquirable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function additionalPhones(): HasMany
    {
        return $this->hasMany(PatientAdditionalPhone::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function activeInquiry(): MorphOne
    {
        return $this->morphOne(PatientInquiry::class, 'inquirable')
            ->active()
            ->orderBy('created_at', 'DESC')
            ->limit(1);
    }

    /**
     * @param $value
     *
     * @return float|int
     */
    public function getSelfPayAttribute($value)
    {
        return money_round($value / 100);
    }

    /**
     * @param $value
     *
     * @return void
     */
    public function setSelfPayAttribute($value)
    {
        $this->attributes['self_pay'] = intval(floatval($value) * 100);
    }

    /**
     * @param $value
     *
     * @return float|int
     */
    public function getVisitCopayAttribute($value)
    {
        return money_round($value / 100);
    }

    /**
     * @param $value
     *
     * @return void
     */
    public function setVisitCopayAttribute($value)
    {
        $this->attributes['visit_copay'] = intval(floatval($value) * 100);
    }

    /**
     * @param $value
     *
     * @return float|int
     */
    public function getDeductibleAttribute($value)
    {
        return money_round($value / 100);
    }

    /**
     * @param $value
     *
     * @return void
     */
    public function setDeductibleAttribute($value)
    {
        $this->attributes['deductible'] = intval(floatval($value) * 100);
    }

    /**
     * @param $value
     *
     * @return float|int
     */
    public function getDeductibleMetAttribute($value)
    {
        return money_round($value / 100);
    }

    /**
     * @param $value
     *
     * @return void
     */
    public function setDeductibleMetAttribute($value)
    {
        $this->attributes['deductible_met'] = intval(floatval($value) * 100);
    }

    /**
     * @param $value
     *
     * @return float|int
     */
    public function getDeductibleRemainingAttribute($value)
    {
        return money_round($value / 100);
    }

    /**
     * @param $value
     *
     * @return void
     */
    public function setDeductibleRemainingAttribute($value)
    {
        $this->attributes['deductible_remaining'] = intval(floatval($value) * 100);
    }

    /**
     * @param $value
     *
     * @return float|int
     */
    public function getInsurancePayAttribute($value)
    {
        return money_round($value / 100);
    }

    /**
     * @param $value
     *
     * @return void
     */
    public function setInsurancePayAttribute($value)
    {
        $this->attributes['insurance_pay'] = intval(floatval($value) * 100);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function patientForms()
    {
        return $this->hasMany(PatientForm::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function templates()
    {
        return $this->hasMany(PatientTemplate::class)->orderBy('position');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function formRequests()
    {
        return $this->hasMany(PatientDocumentRequest::class);
    }

    public function formRequest()
    {
        return $this->hasOne(PatientDocumentRequest::class, 'patient_id', 'id')
            ->orderBy('created_at', 'desc');
    }

    public function tridiuumAppointments()
    {
        return $this->hasMany(KaiserAppointment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tridiuumPatient()
    {
        return $this->hasOne(TridiuumPatient::class, 'internal_id');
    }

    public function eligibilityPayer()
    {
        return $this->belongsTo(EligibilityPayer::class, 'eligibility_payer_id');
    }

    public function preferredLanguage()
    {
        return $this->belongsTo(Language::class, 'preferred_language_id');
    }

    /**
     * faxes
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function faxes(): HasMany
    {
        return $this->hasMany(Fax::class);
    }

    /**
     * faxComments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function faxComments(): HasMany
    {
        return $this->hasMany(FaxComment::class);
    }

    /**
     * faxStatus
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function faxStatus()
    {
        return $this->belongsTo(FaxStatus::class);
    }

    //twilio web hook
    public function twilioSubscribe(): HasOne
    {
        return $this->hasOne(TwilioSubscribe::class);
    }

    public function patientLead(): HasOne
    {
        return $this->hasOne(PatientLead::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(PatientTag::class, 'patient_has_tags', 'patient_id','tag_id');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(PatientTransfer::class, 'patient_id', 'id');
    }

    public function patientHasProviders(): HasMany
    {
        return $this->HasMany(PatientHasProvider::class, 'patients_id', 'id');
    }
    
    public function visitFrequency()
    {
        return $this->belongsTo(PatientVisitFrequency::class);
    }

    public function visitFrequencyChanges()
    {
        return $this->hasMany(PatientVisitFrequencyChange::class);
    }

    public function therapyType()
    {
        return $this->belongsTo(PatientTherapyType::class);
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getEmail()
    {
        if (isset($this->email)) {
            return $this->email;
        }
        if (isset($this->secondary_email)) {
            return $this->secondary_email;
        }

        $informationForm = $this->informationForm;
        if (isset($informationForm)) {
            return $informationForm->email;
        }

        return null;
    }

    public function getLastAppointmentProviderId()
    {
        $appt = Appointment::select('providers_id')
            ->where('patients_id', $this->id)
            ->whereIn('appointment_statuses_id', Status::getActiveCompletedVisitCreatedStatusesId())
            ->orderBy('time', 'desc')
            ->first();

        return optional($appt)->providers_id;
    }

    //filter by patient name
    public function scopeSearch($query, $search)
    {
        return $query->where('first_name', 'LIKE', "%{$search}%")
            ->orWhere('last_name', 'LIKE', "%{$search}%")
            ->orWhereRaw('CONCAT(first_name, " ", last_name) LIKE ? ', "%{$search}%");
    }

    public function scopeWhereNotCompletedIntakeForms($query)
    {
        $paymentForServiceStartDate = PatientDocument::getPaymentForServiceChangeDate();

        $newPatientDocumentTypeId = PatientDocumentType::getNewPatientId();
        $paymentForServiceDocumentTypeId = PatientDocumentType::getPaymentForServiceId();
        $firstNewPatientDocumentOfEachPatientSql = 'SELECT patient_id, MIN(created_at) as min_date FROM patient_documents WHERE document_type_id = ' . $newPatientDocumentTypeId . ' GROUP BY patient_id';
        $firstPaymentForServiceDocumentOfEachPatientSql = 'SELECT patient_id, MIN(created_at) as min_date FROM patient_documents WHERE document_type_id = ' . $paymentForServiceDocumentTypeId . ' GROUP BY patient_id';

        return $query
            ->leftJoin(
                DB::raw("($firstNewPatientDocumentOfEachPatientSql) as first_new_patient_document"),
                'patients.id',
                '=',
                'first_new_patient_document.patient_id'
            )
            ->leftJoin(
                DB::raw("($firstPaymentForServiceDocumentOfEachPatientSql) as first_payment_for_service_document"),
                'patients.id',
                '=',
                'first_payment_for_service_document.patient_id'
            )
            ->where(function ($query) use ($paymentForServiceStartDate) {
                $query
                    ->whereNull('first_new_patient_document.min_date')
                    ->orWhere(function ($query) use ($paymentForServiceStartDate) {
                        $query
                            ->where('first_new_patient_document.min_date', '>=', $paymentForServiceStartDate)
                            ->whereNull('first_payment_for_service_document.min_date');
                    });
            });
    }

    public function scopeWhereCompletedIntakeForms($query)
    {
        $paymentForServiceStartDate = PatientDocument::getPaymentForServiceChangeDate();

        $newPatientDocumentTypeId = PatientDocumentType::getNewPatientId();
        $paymentForServiceDocumentTypeId = PatientDocumentType::getPaymentForServiceId();
        $firstNewPatientDocumentOfEachPatientSql = 'SELECT patient_id, MIN(created_at) as min_date FROM patient_documents WHERE document_type_id = ' . $newPatientDocumentTypeId . ' GROUP BY patient_id';
        $firstPaymentForServiceDocumentOfEachPatientSql = 'SELECT patient_id, MIN(created_at) as min_date FROM patient_documents WHERE document_type_id = ' . $paymentForServiceDocumentTypeId . ' GROUP BY patient_id';

        return $query
            ->leftJoin(
                DB::raw("($firstNewPatientDocumentOfEachPatientSql) as first_new_patient_document"),
                'patients.id',
                '=',
                'first_new_patient_document.patient_id'
            )
            ->leftJoin(
                DB::raw("($firstPaymentForServiceDocumentOfEachPatientSql) as first_payment_for_service_document"),
                'patients.id',
                '=',
                'first_payment_for_service_document.patient_id'
            )
            ->where(function ($query) use ($paymentForServiceStartDate) {
                $query
                    ->whereNotNull('first_new_patient_document.min_date')
                    ->where(function ($query) use ($paymentForServiceStartDate) {
                        $query
                            ->where('first_new_patient_document.min_date', '<', $paymentForServiceStartDate)
                            ->orWhereNotNull('first_payment_for_service_document.min_date');
                    });
            });
    }

    public function getDirtyWithOriginal()
    {
        $dirtyFields = $this->getDirty();

        if (empty(array_except($dirtyFields, self::LOG_FORBIDDEN_FIELDS))) {
            return [];
        }

        $result = [];

        foreach ($dirtyFields as $fieldName => $fieldValue) {
            $result[$fieldName] = [
                'prev' => $this->getOriginal($fieldName),
                'curr' => $fieldValue,
            ];
        }

        return $result;
    }

    public function getLogData()
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'patient_account_number' => $this->patient_account_number,
            'auth_number' => $this->auth_number,
            'first_name' => $this->first_name,
            'home_phone' => $this->home_phone,
            'parse_home_phone' => $this->parse_home_phone,
            'last_name' => $this->last_name,
            'middle_initial' => $this->middle_initial,
            'primary_provider_id' => optional($this->primaryProvider)->id,
            'preferred_language_id' => optional($this->preferredLanguage)->id,
            'email' => $this->email,
            'secondary_email' => $this->secondary_email,
            'insured_name' => $this->insured_name,
            'secondary_insured_name' => $this->secondary_insured_name,
            'address' => $this->address,
            'address_2' => $this->address_2,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'date_of_birth' => $this->date_of_birth,
            'age' => $this->age,
            'cell_phone' => $this->cell_phone,
            'parse_cell_phone' => $this->parse_cell_phone,
            'work_phone' => $this->work_phone,
            'parse_work_phone' => $this->parse_work_phone,
            'preferred_phone' => $this->preferred_phone,
            'visits_auth' => $this->visits_auth,
            'visits_auth_left' => $this->visits_auth_left,
            'primary_insurance' => $this->primary_insurance,
            'primary_insurance_id' => $this->primary_insurance_id,
            'primary_insurance_name' => optional($this->insurance)->insurance,
            'insurance_plan_id' => $this->insurance_plan_id,
            'insurance_plan_name' => optional($this->insurancePlan)->name,
            'eligibility_payer_id' => optional($this->eligibilityPayer)->id,
            'eligibility_payer_name' => optional($this->eligibilityPayer)->name,
            'secondary_insurance' => $this->secondary_insurance,
            'sex' => $this->sex,
            'elig_copay' => $this->elig_copay,
            'elig_status' => $this->elig_status,
            'reffering_provider' => $this->reffering_provider,
            'is_payment_forbidden' => $this->is_payment_forbidden,
            'completed_appointment_count' => $this->completed_appointment_count,
            'watching' => $this->watching,
            'visit_copay' => $this->visit_copay,
            'hidden_on_patients_without_appointments_statistics' => $this->hidden_on_patients_without_appointments_statistics,
            'created_patient_date' => $this->created_patient_date,
            'status_id' => $this->status_id,
            'status_name' => optional($this->status)->status,
            'eff_stop_date' => $this->eff_stop_date,
            'eff_start_date' => $this->eff_start_date,
            'subscriber_id' => $this->subscriber_id,
            'charge_for_cancellation_appointment' => $this->charge_for_cancellation_appointment,
            'is_parsed_cancellation_fee' => $this->is_parsed_cancellation_fee,
            'start_synchronization_time' => $this->start_synchronization_time,
            'tmp_is_manually_archived' => $this->tmp_is_manually_archived,
            'providers' => $this->allProviders()->pluck('id')->toArray(),
        ];
    }

    public function getCreateLogMessage()
    {
        return 'Patient created: ' . $this->getLogMessageIdentifier();
    }

    public function getUpdateLogMessage($dirtyFields = null)
    {
        if (empty($dirtyFields)) {
            $dirtyFields = $this->getDirtyWithOriginal();
        }

        $messagesList = [];

        foreach ($this->getScalarChangeableFields() as $fieldName => $message) {
            if (isset($dirtyFields[$fieldName])) {
                $messagesList[] = "$message changed from '{$dirtyFields[$fieldName]['prev']}' to '{$dirtyFields[$fieldName]['curr']}'";
            }
        }

        if (isset($dirtyFields['primary_insurance_id'])) {
            $prevPrimaryInsurance = PatientInsurance::find($dirtyFields['primary_insurance_id']['prev']);
            $currPrimaryInsurance = PatientInsurance::find($dirtyFields['primary_insurance_id']['curr']);
            $messagesList[] = "Primary insurance id changed from '" . optional($prevPrimaryInsurance)->id . "' to '" . optional($currPrimaryInsurance)->id . "'";
            $messagesList[] = "Primary insurance name changed from '" . optional($prevPrimaryInsurance)->insurance . "' to '" . optional($currPrimaryInsurance)->insurance . "'";
        }

        if (isset($dirtyFields['insurance_plan_id'])) {
            $prevPatientInsurancePlan = PatientInsurancePlan::find($dirtyFields['insurance_plan_id']['prev']);
            $currPatientInsurancePlan = PatientInsurancePlan::find($dirtyFields['insurance_plan_id']['curr']);
            $messagesList[] = "Insurance plan id changed from '" . optional($prevPatientInsurancePlan)->id . "' to '" . optional($currPatientInsurancePlan)->id . "'";
            $messagesList[] = "Insurance plan name changed from '" . optional($prevPatientInsurancePlan)->name . "' to '" . optional($currPatientInsurancePlan)->name . "'";
        }

        if (isset($dirtyFields['eligibility_payer_id'])) {
            $prevEligibilityPayer = EligibilityPayer::find($dirtyFields['eligibility_payer_id']['prev']);
            $currEligibilityPayer = EligibilityPayer::find($dirtyFields['eligibility_payer_id']['curr']);
            $messagesList[] = "Eligibility payer id changed from '" . optional($prevEligibilityPayer)->id . "' to '" . optional($currEligibilityPayer)->id . "'";
            $messagesList[] = "Eligibility payer name changed from '" . optional($prevEligibilityPayer)->name . "' to '" . optional($currEligibilityPayer)->name . "'";
        }

        if (isset($dirtyFields['status_id'])) {
            $prevStatus = PatientStatus::find($dirtyFields['status_id']['prev']);
            $currStatus = PatientStatus::find($dirtyFields['status_id']['curr']);
            $messagesList[] = "Status id changed from '" . optional($prevStatus)->id . "' to '" . optional($currStatus)->id . "'";
            $messagesList[] = "Status name changed from '" . optional($prevStatus)->status . "' to '" . optional($currStatus)->status . "'";
        }

        return 'Patient updated: ' . implode('; ', $messagesList);
    }

    public function getDeleteLogMessage()
    {
        return 'Patient deleted: ' . $this->getLogMessageIdentifier();
    }

    public function getLogMessageIdentifier()
    {
        return "{$this->id}; {$this->patient_id}; '{$this->getFullName()}';"
            . Carbon::parse($this->created_patient_date)->toDateTimeString();
    }

    public function getScalarChangeableFields()
    {
        return [
            'patient_id' => 'Patient id',
            'patient_account_number' => 'Patient account number',
            'auth_number' => 'Auth number',
            'first_name' => 'First name',
            'home_phone' => 'Home phone',
            'parse_home_phone' => 'Parse home phone',
            'last_name' => 'Last name',
            'middle_initial' => 'Middle initial',
            'primary_provider_id' => 'Primary provider id',
            'preferred_language_id' => 'Preferred language id',
            'email' => 'Email',
            'secondary_email' => 'Secondary email',
            'insured_name' => 'Insured name',
            'secondary_insured_name' => 'Secondary insured name',
            'address' => 'Address',
            'address_2' => 'Address 2',
            'city' => 'City',
            'state' => 'State',
            'zip' => 'ZIP',
            'date_of_birth' => 'Date of birth',
            'age' => 'Age',
            'cell_phone' => 'Cell phone',
            'parse_cell_phone' => 'Parse cell phone',
            'work_phone' => 'Work phone',
            'parse_work_phone' => 'Parse work phone',
            'preferred_phone' => 'Preferred phone',
            'visits_auth' => 'Visits auth',
            'visits_auth_left' => 'Visits auth left',
            'primary_insurance' => 'Primary insurance',
            'secondary_insurance' => 'Secondary insurance',
            'sex' => 'Sex',
            'elig_copay' => 'Elig copay',
            'elig_status' => 'Elig status',
            'reffering_provider' => 'Referring provider',
            'is_payment_forbidden' => 'Is payment forbidden',
            'completed_appointment_count' => 'Completed appointment count',
            'watching' => 'Watching',
            'visit_copay' => 'Visit copay',
            'hidden_on_patients_without_appointments_statistics' => 'Hidden on patients without appointments statistics',
            'created_patient_date' => 'Created patient date',
            'eff_stop_date' => 'Eff stop date',
            'eff_start_date' => 'Eff start date',
            'subscriber_id' => 'Subscriber id',
            'charge_for_cancellation_appointment' => 'Charge for cancellation appointment',
            'is_parsed_cancellation_fee' => 'Is parsed cancellation fee',
            'start_synchronization_time' => 'Start synchronization time',
            'tmp_is_manually_archived' => 'Tmp is manually archived',
        ];
    }

    /**
     * Show last eligibility checking date.
     *
     * @return string | null
     */
    public function eligibilityCheckedAt(): ?string
    {
        $result = $this->alerts()->orderBy('date_created', 'desc')->limit(1)->first(['date_created']);
        return isset($result) ? $result->date_created : null;
    }

    /**
     * Give information about is user has appointments after date.
     *
     * @param int $seconds
     *
     * @return bool
     */
    public function hasAppointmentsAfter($seconds)
    {
        return $this->appointments()->where('appointments.time', '>', $seconds)->limit(1)->exists();
    }

    /**
     * Give information about is user has credit cards.
     *
     * @return bool
     */
    public function hasCreditCard()
    {
        return $this->squareAccounts()->rightJoin(
            'patient_square_account_cards as psac',
            'psac.patient_square_account_id',
            '=',
            'patient_square_accounts.id'
        )->limit(1)->exists();
    }

    public function getCreditCardWithFurthestExpirationDate(): ?PatientSquareAccountCard
    {
        return PatientSquareAccountCard::query()
            ->select('patient_square_account_cards.*')
            ->join('patient_square_accounts as psa', 'psa.id', '=', 'patient_square_account_cards.patient_square_account_id')
            ->where('psa.patient_id', $this->id)
            ->orderBy('patient_square_account_cards.exp_year', 'desc')
            ->orderBy('patient_square_account_cards.exp_month', 'desc')
            ->first();
    }

    /**
     * Filter to find patients with filled forms by date.
     *
     * @param Carbon $date
     * @param array $requiredForms
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public static function withFilledForms(Carbon $date, array $requiredForms)
    {
        $formTypeIds = PatientFormType::getFormTypeIds($requiredForms);

        return self::query()
            ->join('patient_document_requests as pdr', 'pdr.patient_id', '=', 'patients.id')
            ->join('patient_document_request_items as pdri', 'pdri.request_id', '=', 'pdr.id')
            ->whereDate('pdr.created_at', '>=', $date->toDateString())
            ->whereNotNull('pdri.filled_at')
            ->whereIn('pdri.form_type_id', $formTypeIds);
    }

    /**
     * @return MorphMany
     */
    public function ringcentralCallLogs(): MorphMany
    {
        return $this->morphMany(RingcentralCallLog::class, 'call_subject');
    }

    /**
     * @return MorphMany
     */
    public function lastFiveRingcentralCallLogs(): MorphMany
    {
        return $this->morphMany(RingcentralCallLog::class, 'call_subject')
            ->orderByDesc('id')
            ->take(5);
    }

    public function lastRingcentralCallLog()
    {
        return $this->morphOne(RingcentralCallLog::class, 'call_subject')
            ->orderByDesc('id');
    }

    public static function getPatientsWithUpcomingReauthorizationQuery()
    {
        $episodeStartDateSql = "SELECT episode_start_date FROM upcoming_reauthorization_requests urr WHERE urr.patient_id=patients.id AND urr.deleted_at IS NULL ORDER BY episode_start_date DESC LIMIT 1";

        return self::select([
            'patients.id',
            \DB::raw("CONCAT(patients.first_name, ' ', patients.last_name) AS patient_name"),
            'patients.eff_stop_date',
            'patient_statuses.status',
            'patient_statuses.hex_color AS status_color',
            'patients.auth_number as insurance_authorization_number',
            'patients.visits_auth as insurance_visits_auth',
            'patients.visits_auth_left as insurance_visits_auth_left',
            'patients.eff_start_date as insurance_eff_start_date',
            'patients.eff_stop_date as insurance_eff_stop_date',
            'patients.primary_insurance',
            'patients.insurance_plan_id',
            DB::raw("($episodeStartDateSql) as episode_start_date")
        ])
            ->join('patient_statuses', 'patients.status_id', '=', 'patient_statuses.id')
            ->join('patient_insurances_plans', 'patients.insurance_plan_id', '=', 'patient_insurances_plans.id')
            ->whereNotNull('patients.insurance_plan_id')
            ->where('patient_insurances_plans.is_verification_required', true)
            ->where('patients.is_test', false)
            ->where(function ($query) {
                $now = Carbon::now();
                $query
                    ->whereNull('patients.auth_number')
                    ->orWhereRaw(
                        "patients.eff_stop_date IS NOT NULL" .
                            " AND DATEDIFF(patients.eff_stop_date, '" . $now . "') <= " . 'patient_insurances_plans.reauthorization_notification_days_count' .
                            " AND DATEDIFF(patients.eff_stop_date, '" . $now . "') > " . config('app.eff_stop_date_depth')
                    )
                    ->orWhereRaw(
                        "patients.visits_auth IS NOT NULL AND patients.visits_auth > 0 AND patients.visits_auth_left IS NOT NULL" .
                            " AND patients.visits_auth_left <= " . 'patient_insurances_plans.reauthorization_notification_visits_count'
                    );
            })
            ->havingRaw('episode_start_date IS NOT NULL')
            ->with('insurancePlan')
            ->orderBy('patients.eff_stop_date', 'asc');
    }

    public function attachTag(int $tagId): void
    {
        if ($this->tags()->where('id', $tagId)->doesntExist()) {
            $this->tags()->attach($tagId);
        }
    }

    public function detachTag(int $tagId): void
    {
        if ($this->tags()->where('id', $tagId)->exists()) {
            $this->tags()->detach($tagId);
        }
    }
    
    public function isInsuranceExpiring()
    {
        $insurancePlan = $this->insurancePlan;

        if (!$insurancePlan->is_verification_required) {
            return false;
        }

        if (!$this->auth_number || !$this->visits_auth_left || !$this->eff_stop_date) {
            return true;
        }

        if ($insurancePlan->reauthorization_notification_visits_count >= $this->visits_auth_left) {
            return true;
        }

        $daysUntilExpiry = Carbon::now()->diffInDays(Carbon::parse($this->eff_stop_date));
        if ($daysUntilExpiry <= $insurancePlan->reauthorization_notification_days_count) {
            return true;
        }

        return false;
    }

    public static function getCollectionNamePatient(): int
    {
        return \Cache::rememberForever('collection-name-patient', function () {
            return DB::connection('mysql_logger')
                ->table('collection_name')
                ->select('id')
                ->where('name', 'Patient')
                ->first()
                ->id;
        });
    }

    public static function getEventNamePatientUpdate(): int
    {
        return \Cache::rememberForever('event-name-patient-update', function () {
            return DB::connection('mysql_logger')
                ->table('event_name')
                ->select('id')
                ->where('name', 'update')
                ->where('collection_name_id', self::getCollectionNamePatient())
                ->first()
                ->id;
        });
    }

    public function getDaysBetweenVisits()
    {
        $visitFrequencyId = $this->visit_frequency_id;

        switch ($visitFrequencyId) {
            case PatientVisitFrequency::getWeeklyId():
                return 7;
            case PatientVisitFrequency::getBiweeklyId():
                return 14;
            default:
                return 0;
        }
    }
}
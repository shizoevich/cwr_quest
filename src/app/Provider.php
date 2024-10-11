<?php

namespace App;

use App\Models\Billing\BillingPeriodType;
use App\Models\FaxModel\Fax;
use App\Models\Patient\PatientElectronicDocument;
use App\Models\Patient\PatientRemovalRequest;
use App\Models\PatientHasProvider;
use App\Models\Provider\Salary;
use App\Models\Provider\SalaryTimesheet;
use App\Models\Provider\SalaryTimesheetLateCancellation;
use App\Models\Provider\SalaryTimesheetVisit;
use App\Models\ProviderComment;
use App\Models\TridiuumProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Carbon\Carbon;

/**
 * App\Provider
 *
 * @property int $id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $middle_initial
 * @property string|null $provider_name
 * @property int|null $work_hours_per_week
 * @property string|null $license_date
 * @property string|null $license_end_date
 * @property int|null $has_benefits
 * @property int|null $is_new
 * @property int $is_supervisor
 * @property int $is_test
 * @property string|null $upheal_user_id external user id from upheal
 * @property string|null $upheal_invite_url
 * @property string|null $upheal_private_room_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $officeally_id
 * @property string|null $license_no
 * @property string|null $individual_npi
 * @property string|null $taxonomy_code
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $phone
 * @property string|null $tridiuum_username
 * @property string|null $tridiuum_password
 * @property string|null $tridiuum_credentials_failed_at
 * @property bool $tridiuum_sync_availability
 * @property bool $tridiuum_sync_appointments
 * @property int|null $billing_period_type_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\AppointmentNotification[] $appointmentNotifications
 * @property-read int|null $appointment_notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Appointment[] $appointments
 * @property-read int|null $appointments_count
 * @property-read BillingPeriodType|null $billingPeriodType
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientComment[] $comments
 * @property-read int|null $comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientDocumentComment[] $documentComments
 * @property-read int|null $document_comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|PatientElectronicDocument[] $electronicDocuments
 * @property-read int|null $electronic_documents_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Fax[] $faxes
 * @property-read int|null $faxes_count
 * @property-read bool $is_tridiuum_credentials_invalid
 * @property-read mixed $tariff_plan
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientInsurance[] $insurances
 * @property-read int|null $insurances_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\KaiserAppointment[] $kaiserAppointments
 * @property-read int|null $kaiser_appointments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Patient[] $patients
 * @property-read int|null $patients_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientNote[] $progressNotes
 * @property-read int|null $progress_notes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Patient[] $readOnlyPatients
 * @property-read int|null $read_only_patients_count
 * @property-read \Illuminate\Database\Eloquent\Collection|PatientRemovalRequest[] $removalRequests
 * @property-read int|null $removal_requests_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Salary[] $salary
 * @property-read int|null $salary_count
 * @property-read \Illuminate\Database\Eloquent\Collection|SalaryTimesheetLateCancellation[] $salaryTimesheetLateCancellations
 * @property-read int|null $salary_timesheet_late_cancellations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|SalaryTimesheetVisit[] $salaryTimesheetVisits
 * @property-read int|null $salary_timesheet_visits_count
 * @property-read \Illuminate\Database\Eloquent\Collection|SalaryTimesheet[] $salaryTimesheets
 * @property-read int|null $salary_timesheets_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientDocumentShared[] $sharedDocuments
 * @property-read int|null $shared_documents_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\TariffPlan[] $tariffPlan
 * @property-read int|null $tariff_plan_count
 * @property-read TridiuumProvider|null $tridiuumProvider
 * @property-read \App\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $users
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientVisit[] $visits
 * @property-read int|null $visits_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Availability[] $workHours
 * @property-read int|null $work_hours_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ProviderWorkHour[] $workHoursOld
 * @property-read int|null $work_hours_old_count
 * @method static Builder|Provider newModelQuery()
 * @method static Builder|Provider newQuery()
 * @method static Builder|Provider notTest()
 * @method static \Illuminate\Database\Query\Builder|Provider onlyTrashed()
 * @method static Builder|Provider providerNames()
 * @method static Builder|Provider query()
 * @method static Builder|Provider search($name)
 * @method static Builder|Provider whereBillingPeriodTypeId($value)
 * @method static Builder|Provider whereCreatedAt($value)
 * @method static Builder|Provider whereDeletedAt($value)
 * @method static Builder|Provider whereFirstName($value)
 * @method static Builder|Provider whereHasBenefits($value)
 * @method static Builder|Provider whereId($value)
 * @method static Builder|Provider whereIndividualNpi($value)
 * @method static Builder|Provider whereIsNew($value)
 * @method static Builder|Provider whereIsSupervisor($value)
 * @method static Builder|Provider whereIsTest($value)
 * @method static Builder|Provider whereLastName($value)
 * @method static Builder|Provider whereLicenseDate($value)
 * @method static Builder|Provider whereLicenseEndDate($value)
 * @method static Builder|Provider whereLicenseNo($value)
 * @method static Builder|Provider whereMiddleInitial($value)
 * @method static Builder|Provider whereOfficeallyId($value)
 * @method static Builder|Provider wherePhone($value)
 * @method static Builder|Provider whereProviderName($value)
 * @method static Builder|Provider whereTaxonomyCode($value)
 * @method static Builder|Provider whereTridiuumCredentialsFailedAt($value)
 * @method static Builder|Provider whereTridiuumPassword($value)
 * @method static Builder|Provider whereTridiuumSyncAppointments($value)
 * @method static Builder|Provider whereTridiuumSyncAvailability($value)
 * @method static Builder|Provider whereTridiuumUsername($value)
 * @method static Builder|Provider whereUpdatedAt($value)
 * @method static Builder|Provider whereUphealInviteUrl($value)
 * @method static Builder|Provider whereUphealPrivateRoomLink($value)
 * @method static Builder|Provider whereUphealUserId($value)
 * @method static Builder|Provider whereWorkHoursPerWeek($value)
 * @method static \Illuminate\Database\Query\Builder|Provider withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Provider withoutTrashed()
 * @mixin \Eloquent
 */
class Provider extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'providers';

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    protected $hidden = ['tridiuum_password', 'tridiuum_username', 'tridiuum_credentials_failed_at'];
    
    protected $casts = [
        'tridiuum_sync_availability' => 'bool',
        'tridiuum_sync_appointments' => 'bool',
    ];

    const KAISER_TYPE_ACTIVE = 'Active';
    const KAISER_TYPE_INACTIVE = 'Inactive';
    const KAISER_TYPES = [
        self::KAISER_TYPE_ACTIVE,
        self::KAISER_TYPE_INACTIVE,
    ];

    const TRIDIUUM_NAME_EXCEPTIONAL_CASES = [
        'Debbie Grace Lugtu' => 'Debbie Lugtu',
    ];

    public function appointments()
    {
        return $this->hasMany('App\Appointment', 'providers_id', 'id');
    }

    /**
     * @return bool
     */
    public function getIsTridiuumCredentialsInvalidAttribute(): bool
    {
        return $this->tridiuum_credentials_failed_at !== null;
    }

    public function kaiserAppointments()
    {
        return $this->hasMany('App\KaiserAppointment', 'provider_id', 'id');
    }

    public function appointmentNotifications()
    {
        return $this->hasMany('App\AppointmentNotification', 'provider_id', 'id');
    }

    public function patients()
    {
        return $this->belongsToMany('App\Patient', 'patients_has_providers', 'providers_id', 'patients_id')
            ->where('chart_read_only', false);
    }

    public function readOnlyPatients()
    {
        return $this->belongsToMany('App\Patient', 'patients_has_providers', 'providers_id', 'patients_id')
            ->where('chart_read_only', true);
    }

    public function progressNotes() {
        return $this->hasMany('App\PatientNote', 'provider_id', 'id');
    }

    public function sharedDocuments() {
        return $this->hasMany(PatientDocumentShared::class, 'provider_id');
    }

    public function user() {
        return $this->hasOne('\App\User', 'provider_id', 'id');
    }

    public function users() {
        return $this->hasMany('\App\User', 'provider_id', 'id');
    }

    public function tariffPlan()
    {
        return $this->belongsToMany('App\TariffPlan', 'providers_tariffs_plans', 'provider_id', 'tariff_plan_id');
    }

    public function getTariffPlanAttribute()
    {
        return $this->tariffPlan()->first();
    }

    public function scopeSearch($query, $name) {
        $name = trim(strtolower($name));
        return $query->where('provider_name', 'like', "%$name%");
    }

    public function scopeProviderNames($query) {
        return $query->select(['provider_name', 'id'])->orderBy('provider_name');
    }

    public function scopeNotTest($query)
    {
        return $query->where('is_test', '=', 0);
    }

    public function workHours()
    {
        return $this->hasMany('App\Availability', 'provider_id', 'id');
    }

    public function workHoursOld()
    {
        return $this->hasMany('App\ProviderWorkHour', 'provider_id', 'id');
    }

    public function insurances()
    {
        return $this->belongsToMany(PatientInsurance::class, 'provider_insurances', 'provider_id', 'insurance_id');
    }

    public function visits()
    {
        return $this->hasMany(PatientVisit::class, 'provider_id', 'id');
    }


    public function comments() {
        return $this->hasMany(PatientComment::class, 'provider_id', 'id');
    }

    public function profileComments() {
        return $this->hasMany(ProviderComment::class, 'provider_id', 'id');
    }

    public function documentComments() {
        return $this->hasMany(PatientDocumentComment::class, 'provider_id', 'id');
    }

    public function removalRequests()
    {
        return $this->hasMany(PatientRemovalRequest::class);
    }

    /**
     * @param null  $providerId
     * @param array $statuses
     *
     * @return array
     */
    public static function getPatientsWithMissingNotes($providerId = null, array $statuses = [], array $patientIds = [], Carbon $fromDate = null): array
    {
        $appointmentStatuses = Status::getCompletedVisitCreatedStatusesId();
        
        return Appointment::query()
            ->select([
                'patients.id',
                'patients.first_name',
                'patients.last_name',
                'patients.primary_insurance',
                'patient_statuses.status',
                'patient_statuses.hex_color',
                'patients_has_providers.providers_id',
                \DB::raw('COUNT(*) AS missing_note_count'),
                \DB::raw("GROUP_CONCAT(appointments.time ORDER BY appointments.time SEPARATOR ',') AS missing_note_dates"),
            ])
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->join('patient_statuses', 'patients.status_id', '=', 'patient_statuses.id')
            ->join('patients_has_providers', 'patients_has_providers.patients_id', '=', 'patients.id')
            ->leftJoin('patient_notes', function(JoinClause $join) {
                $join->on($join->table . '.appointment_id', '=', 'appointments.id')
                    ->whereNull($join->table . '.deleted_at');
            })
            ->whereIn('appointments.appointment_statuses_id', $appointmentStatuses)
            ->where('patients.is_test', '=', 0)
            ->where('appointments.note_on_paper', '=', 0)
            ->where('appointments.is_initial', '=', 0)
            ->whereNull('appointments.initial_assessment_id')
            ->where(function(Builder $query) {
                $query->whereNull('patient_notes.id')->orWhere('patient_notes.is_finalized', false);
            })
            ->where('patients_has_providers.providers_id', '=', \DB::raw('appointments.providers_id'))
            ->where('patients_has_providers.chart_read_only', '=', false)
            ->when(!empty($fromDate), function(Builder $query) use ($fromDate) {
                $query->whereRaw("DATE(FROM_UNIXTIME(`appointments`.`time`)) >= '{$fromDate->toDateString()}'");
            })
            ->when(!empty($providerId), function(Builder $query) use ($providerId) {
                $query->where('appointments.providers_id', '=', $providerId);
            })
            ->when(!empty($statuses), function(Builder $query) use ($statuses) {
                $query->whereIn('patients.status_id', $statuses);
            })
            ->when(!empty($patientIds), function(Builder $query) use ($patientIds) {
                $query->whereIn('patients.id', $patientIds);
            })
            ->groupBy(['patients.id'])
            ->orderBy('patients.first_name')
            ->orderBy('patients.last_name')
            ->get()
            ->toArray();
    }

    public static function getPatientsWithMissingNotesGroupedByProviders(array $statuses = [], array $patientIds = [], Carbon $fromDate = null): array
    {
        $appointmentStatuses = Status::getCompletedVisitCreatedStatusesId();
        
        return Appointment::query()
            ->select([
                'patients.id',
                'patients.first_name',
                'patients.last_name',
                'patients.primary_insurance',
                'patient_statuses.status',
                'patient_statuses.hex_color',
                'patients_has_providers.providers_id',
                'providers.provider_name',
                \DB::raw('COUNT(*) AS missing_note_count'),
                \DB::raw("GROUP_CONCAT(appointments.time ORDER BY appointments.time SEPARATOR ',') AS missing_note_dates"),
            ])
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->join('patient_statuses', 'patients.status_id', '=', 'patient_statuses.id')
            ->join('patients_has_providers', 'patients_has_providers.patients_id', '=', 'patients.id')
            ->join('providers', 'providers.id', '=', 'appointments.providers_id')
            ->leftJoin('patient_notes', function(JoinClause $join) {
                $join->on($join->table . '.appointment_id', '=', 'appointments.id')
                    ->whereNull($join->table . '.deleted_at');
            })
            ->whereIn('appointments.appointment_statuses_id', $appointmentStatuses)
            ->where('patients.is_test', '=', 0)
            ->where('appointments.note_on_paper', '=', 0)
            ->where('appointments.is_initial', '=', 0)
            ->whereNull('appointments.initial_assessment_id')
            ->where(function(Builder $query) {
                $query->whereNull('patient_notes.id')->orWhere('patient_notes.is_finalized', false);
            })
            ->where('patients_has_providers.providers_id', '=', \DB::raw('appointments.providers_id'))
            ->where('patients_has_providers.chart_read_only', '=', false)
            ->when(!empty($fromDate), function(Builder $query) use ($fromDate) {
                $query->whereRaw("DATE(FROM_UNIXTIME(`appointments`.`time`)) >= '{$fromDate->toDateString()}'");
            })
            ->when(!empty($statuses), function(Builder $query) use ($statuses) {
                $query->whereIn('patients.status_id', $statuses);
            })
            ->when(!empty($patientIds), function(Builder $query) use ($patientIds) {
                $query->whereIn('patients.id', $patientIds);
            })
            ->groupBy(['patients.id', 'providers.id'])
            ->orderBy('patients.first_name')
            ->orderBy('patients.last_name')
            ->get()
            ->toArray();
    }

    public static function getPatientsWithMissingInitialAssessments($providerId = null, array $statuses = [], array $patientIds = [], Carbon $fromDate = null): array
    {
        $appointmentStatuses = Status::getCompletedVisitCreatedStatusesId();
        
        return Appointment::query()
            ->select([
                'patients.id',
                'patients.first_name',
                'patients.last_name',
                'patients.primary_insurance',
                'patient_statuses.status',
                'patient_statuses.hex_color',
                'patients_has_providers.providers_id',
                \DB::raw('COUNT(*) AS missing_initial_assessments_count'),
                \DB::raw("GROUP_CONCAT(appointments.time ORDER BY appointments.time SEPARATOR ',') AS missing_initial_assessments_dates"),
            ])
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->join('patient_statuses', 'patients.status_id', '=', 'patient_statuses.id')
            ->join('patients_has_providers', 'patients_has_providers.patients_id', '=', 'patients.id')
            ->leftJoin('patient_notes', function(JoinClause $join) {
                $join->on($join->table . '.appointment_id', '=', 'appointments.id')
                    ->whereNull($join->table . '.deleted_at');
            })
            ->whereIn('appointments.appointment_statuses_id', $appointmentStatuses)
            ->where('patients.is_test', '=', 0)
            ->where('appointments.note_on_paper', '=', 0)
            ->where('appointments.is_initial', '=', 1)
            ->whereNull('appointments.initial_assessment_id')
            ->where(function(Builder $query) {
                $query->whereNull('patient_notes.id')->orWhere('patient_notes.is_finalized', false);
            })
            ->where('patients_has_providers.providers_id', '=', \DB::raw('appointments.providers_id'))
            ->where('patients_has_providers.chart_read_only', '=', false)
            ->when(!empty($fromDate), function(Builder $query) use ($fromDate) {
                $query->whereRaw("DATE(FROM_UNIXTIME(`appointments`.`time`)) >= '{$fromDate->toDateString()}'");
            })
            ->when(!empty($providerId), function(Builder $query) use ($providerId) {
                $query->where('appointments.providers_id', '=', $providerId);
            })
            ->when(!empty($statuses), function(Builder $query) use ($statuses) {
                $query->whereIn('patients.status_id', $statuses);
            })
            ->when(!empty($patientIds), function(Builder $query) use ($patientIds) {
                $query->whereIn('patients.id', $patientIds);
            })
            ->groupBy(['patients.id'])
            ->orderBy('patients.first_name')
            ->orderBy('patients.last_name')
            ->get()
            ->toArray();
    }

    public static function getPatientsWithMissingInitialAssessmentsGroupedByProviders(array $statuses = [], array $patientIds = [], Carbon $fromDate = null): array
    {
        $appointmentStatuses = Status::getCompletedVisitCreatedStatusesId();
        
        return Appointment::query()
            ->select([
                'patients.id',
                'patients.first_name',
                'patients.last_name',
                'patients.primary_insurance',
                'patient_statuses.status',
                'patient_statuses.hex_color',
                'patients_has_providers.providers_id',
                'providers.provider_name',
                \DB::raw('COUNT(*) AS missing_initial_assessments_count'),
                \DB::raw("GROUP_CONCAT(appointments.time ORDER BY appointments.time SEPARATOR ',') AS missing_initial_assessments_dates"),
            ])
            ->join('patients', 'patients.id', '=', 'appointments.patients_id')
            ->join('patient_statuses', 'patients.status_id', '=', 'patient_statuses.id')
            ->join('patients_has_providers', 'patients_has_providers.patients_id', '=', 'patients.id')
            ->join('providers', 'providers.id', '=', 'appointments.providers_id')
            ->leftJoin('patient_notes', function(JoinClause $join) {
                $join->on($join->table . '.appointment_id', '=', 'appointments.id')
                    ->whereNull($join->table . '.deleted_at');
            })
            ->whereIn('appointments.appointment_statuses_id', $appointmentStatuses)
            ->where('patients.is_test', '=', 0)
            ->where('appointments.note_on_paper', '=', 0)
            ->where('appointments.is_initial', '=', 1)
            ->whereNull('appointments.initial_assessment_id')
            ->where(function(Builder $query) {
                $query->whereNull('patient_notes.id')->orWhere('patient_notes.is_finalized', false);
            })
            ->where('patients_has_providers.providers_id', '=', \DB::raw('appointments.providers_id'))
            ->where('patients_has_providers.chart_read_only', '=', false)
            ->when(!empty($fromDate), function(Builder $query) use ($fromDate) {
                $query->whereRaw("DATE(FROM_UNIXTIME(`appointments`.`time`)) >= '{$fromDate->toDateString()}'");
            })
            ->when(!empty($statuses), function(Builder $query) use ($statuses) {
                $query->whereIn('patients.status_id', $statuses);
            })
            ->when(!empty($patientIds), function(Builder $query) use ($patientIds) {
                $query->whereIn('patients.id', $patientIds);
            })
            ->groupBy(['patients.id', 'providers.id'])
            ->orderBy('patients.first_name')
            ->orderBy('patients.last_name')
            ->get()
            ->toArray();
    }
    
    public function tridiuumProvider()
    {
        return $this->hasOne(TridiuumProvider::class, 'internal_id', 'id');
    }
    
    public function billingPeriodType()
    {
        return $this->belongsTo(BillingPeriodType::class, 'billing_period_type_id');
    }
    
    public function electronicDocuments()
    {
        return $this->hasMany(PatientElectronicDocument::class, 'provider_id');
    }
    
    public function salary()
    {
        return $this->hasMany(Salary::class, 'provider_id');
    }
    
    public function salaryTimesheetVisits()
    {
        return $this->hasMany(SalaryTimesheetVisit::class, 'provider_id');
    }
    
    public function salaryTimesheetLateCancellations()
    {
        return $this->hasMany(SalaryTimesheetLateCancellation::class, 'provider_id');
    }
    
    public function salaryTimesheets()
    {
        return $this->hasMany(SalaryTimesheet::class, 'provider_id');
    }

    public function faxes()
    {
        return $this->hasMany(Fax::class);
    }

    public function getTherapistFullname()
    {
        $user = $this->user;
        if (isset($user)) {
            $therapistSurvey = $user->therapistSurvey;
            if (isset($therapistSurvey)) {
                return $therapistSurvey->getFullname();
            }
        }

        return $this->provider_name;
    }

    public function checkSupervisorAccessToPatient(int $patientId)
    {
        return PatientHasProvider::query()
            ->where('patients_id', $patientId)
            ->where('providers_id', $this->id)
            ->where('chart_read_only', true)
            ->whereNotNull('supervisee_id')
            ->exists();
    }

    public static function sanitizeTridiuumProviderName(string $providerName): string
    {
        if (empty($providerName)) {
            return '';
        }

        if (array_key_exists($providerName, self::TRIDIUUM_NAME_EXCEPTIONAL_CASES)) {
            return self::TRIDIUUM_NAME_EXCEPTIONAL_CASES[$providerName];
        }

        $providerName = preg_replace([
            '/\(Change\s+Within\s+Reach\)/u',
            '/Ms\./',
            '/Mrs\./',
            '/Mr\./',
            '/Miss/',
            '/MFT/',
            '/ASF/',
            '/PhD/',
            '/PsyD/',
            '/LMFT/',
            '/LCSW/',
            '/ACSW/',
            '/AMFT/',
        ], '', $providerName);

        $providerName = preg_replace('/\s+/u', ' ', $providerName);

        if (str_contains($providerName, ',')) {
            $providerName = explode(',', $providerName)[0];
        }

        return trim($providerName);
    }
}

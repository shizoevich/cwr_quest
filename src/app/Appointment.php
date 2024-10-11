<?php

namespace App;

use App\Models\AppointmentPayment;
use App\Models\GoogleMeeting;
use App\Models\LateCancellationTransaction;
use App\Models\Officeally\OfficeallyTransaction;
use App\Models\Square\SquareTransaction;
use App\Models\UphealMeeting;
use App\Models\Patient\PatientTemplate;
use App\Models\RingcentralCallLog;
use App\Traits\Filters\ProviderScope;
use App\Contracts\Models\Appointment as AppointmentContract;
use App\Models\TreatmentModality;
use App\Models\AppointmentRescheduleSubStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Appointment
 *
 * @property int $id
 * @property int|null $idAppointments
 * @property int|null $rescheduled_appointment_id Indicates the id of the appointment that was rescheduled
 * @property string|null $resource
 * @property int|null $time
 * @property int $is_initial
 * @property string|null $initial_assessment_type
 * @property int|null $initial_assessment_id
 * @property string|null $initial_assessment_created_at
 * @property string|null $check_in
 * @property string|null $date_created
 * @property float|int $visit_copay
 * @property int|null $visit_length
 * @property string|null $notes
 * @property string|null $custom_notes Field for storing notes, written when complete/cancel appointment from EHR
 * @property string|null $reason_for_visit
 * @property string|null $sheldued_by
 * @property int|null $patients_id
 * @property int|null $providers_id
 * @property int|null $offices_id
 * @property int|null $office_room_id
 * @property int|null $appointment_statuses_id
 * @property int|null $reminder_status_id
 * @property int $is_paid
 * @property int $is_created_by_tridiuum
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $parsed_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $not_found_count Количество раз когда парсер не смог спарсить этот аппойтмент
 * @property bool $payed
 * @property int $note_on_paper
 * @property string|null $start_completing_date
 * @property string|null $start_creating_visit
 * @property int|null $new_status_id
 * @property bool $is_creating_visit_inprogress
 * @property int $is_warning To display the type of error on the front
 * @property string|null $error_message
 * @property bool|null $progress_note_complete
 * @property-read \App\AppointmentNotification|null $appointmentNotification
 * @property-read bool $can_cancel
 * @property-read bool $can_change_status
 * @property-read bool $can_complete
 * @property-read bool $can_reschedule
 * @property-read array $date_of_service
 * @property-read GoogleMeeting|null $googleMeet
 * @property-read Model|\Eloquent $initialAssessment
 * @property-read \App\Office|null $office
 * @property-read \App\OfficeRoom|null $officeRoom
 * @property-read \App\Patient|null $patient
 * @property-read \App\PatientNote|null $patientNote
 * @property-read \Illuminate\Database\Eloquent\Collection|PatientTemplate[] $patientTemplates
 * @property-read int|null $patient_templates_count
 * @property-read \Illuminate\Database\Eloquent\Collection|AppointmentPayment[] $paymentInfo
 * @property-read int|null $payment_info_count
 * @property-read \App\Provider|null $provider
 * @property-read \Illuminate\Database\Eloquent\Collection|RingcentralCallLog[] $ringcentralCallLogs
 * @property-read int|null $ringcentral_call_logs_count
 * @property-read \App\Status|null $status
 * @property-read UphealMeeting|null $uphealMeet
 * @property-read \App\PatientVisit|null $visit
 * @method static Builder|Appointment activeForPeriod(\Carbon\Carbon $from, \Carbon\Carbon $to)
 * @method static Builder|Appointment forPeriod(\Carbon\Carbon $from, \Carbon\Carbon $to)
 * @method static Builder|Appointment inCurrentWeek()
 * @method static Builder|Appointment newModelQuery()
 * @method static Builder|Appointment newQuery()
 * @method static Builder|Appointment onlyActive()
 * @method static Builder|Appointment onlyCompleted()
 * @method static \Illuminate\Database\Query\Builder|Appointment onlyTrashed()
 * @method static Builder|Appointment onlyVisitCreated()
 * @method static Builder|Appointment query()
 * @method static Builder|Appointment statusNotCancel()
 * @method static Builder|Appointment whereAppointmentStatusesId($value)
 * @method static Builder|Appointment whereCheckIn($value)
 * @method static Builder|Appointment whereCreatedAt($value)
 * @method static Builder|Appointment whereCustomNotes($value)
 * @method static Builder|Appointment whereDateCreated($value)
 * @method static Builder|Appointment whereDeletedAt($value)
 * @method static Builder|Appointment whereErrorMessage($value)
 * @method static Builder|Appointment whereId($value)
 * @method static Builder|Appointment whereIdAppointments($value)
 * @method static Builder|Appointment whereInitialAssessmentCreatedAt($value)
 * @method static Builder|Appointment whereInitialAssessmentId($value)
 * @method static Builder|Appointment whereInitialAssessmentType($value)
 * @method static Builder|Appointment whereInsurance($insuranceId)
 * @method static Builder|Appointment whereIsCreatedByTridiuum($value)
 * @method static Builder|Appointment whereIsCreatingVisitInprogress($value)
 * @method static Builder|Appointment whereIsInitial($value)
 * @method static Builder|Appointment whereIsPaid($value)
 * @method static Builder|Appointment whereIsWarning($value)
 * @method static Builder|Appointment whereNewStatusId($value)
 * @method static Builder|Appointment whereNotFoundCount($value)
 * @method static Builder|Appointment whereNoteOnPaper($value)
 * @method static Builder|Appointment whereNotes($value)
 * @method static Builder|Appointment whereOffice($officeId)
 * @method static Builder|Appointment whereOfficeRoomId($value)
 * @method static Builder|Appointment whereOfficesId($value)
 * @method static Builder|Appointment whereParsedAt($value)
 * @method static Builder|Appointment wherePatientsId($value)
 * @method static Builder|Appointment wherePayed($value)
 * @method static Builder|Appointment whereProgressNoteComplete($value)
 * @method static Builder|Appointment whereProvider($providerId)
 * @method static Builder|Appointment whereProviderAgeGroups($ageGroupIdAll)
 * @method static Builder|Appointment whereProviderEthnicities($ethnicities)
 * @method static Builder|Appointment whereProviderFocus($focusIdAll)
 * @method static Builder|Appointment whereProviderLanguages($languages)
 * @method static Builder|Appointment whereProviderPatientCategories($patientCategories)
 * @method static Builder|Appointment whereProviderRaces($races)
 * @method static Builder|Appointment whereProviderSpecialties($specialties)
 * @method static Builder|Appointment whereProviderTreatmentTypes($treatmentTypes)
 * @method static Builder|Appointment whereProviderTypesOfClients($typesOfClientsIdAll)
 * @method static Builder|Appointment whereProvidersId($value)
 * @method static Builder|Appointment whereReasonForVisit($value)
 * @method static Builder|Appointment whereReminderStatusId($value)
 * @method static Builder|Appointment whereRescheduledAppointmentId($value)
 * @method static Builder|Appointment whereResource($value)
 * @method static Builder|Appointment whereShelduedBy($value)
 * @method static Builder|Appointment whereStartCompletingDate($value)
 * @method static Builder|Appointment whereStartCreatingVisit($value)
 * @method static Builder|Appointment whereTime($value)
 * @method static Builder|Appointment whereUpdatedAt($value)
 * @method static Builder|Appointment whereVisitCopay($value)
 * @method static Builder|Appointment whereVisitLength($value)
 * @method static Builder|Appointment whereVisitType($visitTypes)
 * @method static Builder|Appointment withPatientAppointmentsCount()
 * @method static Builder|Appointment withTherapistSurvey()
 * @method static \Illuminate\Database\Query\Builder|Appointment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Appointment withoutTrashed()
 * @mixin \Eloquent
 */
class Appointment extends Model implements AppointmentContract
{
    use SoftDeletes;
    use ProviderScope;

    public const REASON_TELEHEALTH = 'Telehealth';
    public const REASON_INDIVIDUAL_PSYCHOTHERAPY = 'Individual Psychotherapy';

    public const TELEHEALTH_PROVIDER_UPHEAL = 'upheal';
    public const TELEHEALTH_PROVIDER_GOOGLE_MEET = 'google_meet';

    public const DEFAULT_VISIT_LENGTH = 60;

    protected $dates = ['deleted_at'];

    protected $table = 'appointments';

    protected $appends = [
        'date_of_service',
    ];

    protected $fillable = [
        'idAppointments',
        'resource',
        'time',
        'is_initial',
        'check_in',
        'date_created',
        'visit_copay',
        'visit_length',
        'notes',
        'custom_notes',
        'reason_for_visit',
        'treatment_modality_id',
        'sheldued_by',
        'patients_id',
        'providers_id',
        'offices_id',
        'office_room_id',
        'appointment_statuses_id',
        'reschedule_sub_status_id',
        'is_paid',
        'parsed_at',
        'not_found_count',
        'payed',
        'note_on_paper',
        'start_completing_date',
        'start_creating_visit',
        'is_creating_visit_inprogress',
        'is_warning',
        'error_message',
        'initial_assessment_type',
        'initial_assessment_id',
        'initial_assessment_created_at',
        'is_created_by_tridiuum',
        'progress_note_complete',
    ];
    protected $casts = [
        'idAppointments' => 'integer',
        'time' => 'integer',
        'visit_length' => 'integer',
        'offices_id' => 'integer',
        'office_room_id' => 'integer',
        'providers_id' => 'integer',
        'initial_assessment_id' => 'integer',

        'payed' => 'boolean',
        'note_in_paper' => 'boolean',
        'is_creating_visit_inprogress' => 'boolean',
        'progress_note_complete'=> 'boolean',
    ];

    private const LOG_FORBIDDEN_FIELDS = [
        'parsed_at',
        'updated_at',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'offices_id', 'id');
    }

    public function officeRoom(): BelongsTo
    {
        return $this->belongsTo(OfficeRoom::class, 'office_room_id', 'id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patients_id', 'id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'providers_id', 'id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'appointment_statuses_id', 'id');
    }

    public function rescheduleSubStatus(): BelongsTo
    {
        return $this->belongsTo(AppointmentRescheduleSubStatus::class, 'reschedule_sub_status_id', 'id');
    }

    public function paymentInfo(): HasMany
    {
        return $this->hasMany(AppointmentPayment::class, 'appointment_id');
    }

    public function appointmentNotification(): HasOne
    {
        return $this->hasOne(AppointmentNotification::class, 'appointment_id', 'id');
    }

    public function scopeOnlyVisitCreated($query)
    {
        return $query->where('appointment_statuses_id', '=', Status::getVisitCreatedId());
    }

    public function scopeOnlyCompleted($query)
    {
        return $query->where('appointment_statuses_id', '=', Status::getCompletedId());
    }

    public function scopeOnlyActive($query)
    {
        return $query->where('appointment_statuses_id', '=', Status::getActiveId());
    }

    public function scopeWhereProvider($query, $providerId)
    {
        // ToDo: add strict comparison?
        if ($providerId == 0) {
            return $query;
        }

        return $query->where('providers_id', $providerId);
    }

    public function scopeWhereOffice($query, $officeId)
    {
        // ToDo: add strict comparison?
        if ($officeId == 0) {
            return $query;
        }

        return $query->where('offices_id', $officeId);
    }

    public function scopeWithTherapistSurvey($query)
    {
        return $query->join('users', 'providers_id', '=', 'users.provider_id')
            ->join('therapist_survey', 'users.id', '=', 'therapist_survey.user_id');
    }

    public function scopeInCurrentWeek($query)
    {

        return $query->havingRaw("WEEK(FROM_UNIXTIME(time)) = WEEK(NOW())")
            ->havingRaw("YEAR(FROM_UNIXTIME(time)) = YEAR(NOW())");
    }

    public function scopeWhereInsurance($query, $insuranceId)
    {
        // ToDo: add strict comparison?
        if ($insuranceId == 0) {
            return $query;
        }

        return $query->join('patients', function ($join) use ($insuranceId) {
            $join->on('patients.id', '=', 'appointments.patients_id')
                ->where('patients.primary_insurance_id', '=', $insuranceId);
        });

        return $query;
    }

    public function scopeStatusNotCancel($query)
    {
        return $query->whereNotIn('appointment_statuses_id', Status::getOtherCancelStatusesId());
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
    public function setVisitCopayAttribute($value): void
    {
        $this->attributes['visit_copay'] = $value * 100;
    }

    public function patientTemplates(): HasMany
    {
        return $this->hasMany(PatientTemplate::class, 'patient_id', 'patients_id');
    }

    public function visit(): HasOne
    {
        return $this->hasOne(PatientVisit::class, 'appointment_id');
    }

    /**
     * @return Builder
     */
    public static function getBasePastAppointmentsQuery(): Builder
    {
        $endDate = Carbon::today()->subDay()->endOfDay();
        $startDate = $endDate->copy()->subMonths(2)->startOfDay();

        return self::query()
            ->where('appointment_statuses_id', Status::getActiveId())
            ->where('appointments.providers_id', auth()->user()->provider_id)
            ->where('appointments.time', '>=', $startDate->timestamp)
            ->where('appointments.time', '<=', $endDate->timestamp)
            ->where(function ($query) {
                $query->whereNull('appointments.start_completing_date')
                    ->orWhere('appointments.start_completing_date', '<', Carbon::now()->subHour()->toDateTimeString());
            });
    }

    public function initialAssessment(): MorphTo
    {
        return $this->morphTo();
    }

    public function googleMeet(): HasOne
    {
        return $this->hasOne(GoogleMeeting::class);
    }

    public function uphealMeet(): HasOne
    {
        return $this->hasOne(UphealMeeting::class);
    }

    public function patientNote(): HasOne
    {
        return $this->hasOne(PatientNote::class);
    }

    public function squareTransaction(): HasOne
    {
        return $this->hasOne(SquareTransaction::class, 'appointment_id', 'id');
    }

    public function lateCancellationTransaction(): HasOne
    {
        return $this->hasOne(LateCancellationTransaction::class, 'appointment_id', 'id');
    }

    public function officeallyTransaction(): HasOne
    {
        return $this->hasOne(OfficeallyTransaction::class, 'appointment_id', 'id');
    }

    public function getDateOfServiceAttribute(): array
    {
        $status = $this->status;
        
        if (is_object($status)) {
            $status = $status->status;
        }

        $time = $this->getAttributes()['time'] ?? null;

        return [
            'date' => !empty($time) ? Carbon::createFromTimestamp($time)->format('m/d/Y') : null,
            'time' => !empty($time) ? Carbon::createFromTimestamp($time)->format('g:i A') : null,
            'text' => (!empty($time) ? Carbon::createFromTimestamp($time)->format('m/d/Y') : null)
                . ($status ? ' (' . $status . ')' : null),
            'value' => $this->id
        ];
    }

    /**
     * @return bool
     */
    public function getCanChangeStatusAttribute(): bool
    {
        return $this->appointment_statuses_id != Status::getVisitCreatedId()
            && $this->appointment_statuses_id != Status::getActiveId();
    }

    /**
     * @return bool
     */
    public function getCanCompleteAttribute()
    {
        $time = $this->getAttributes()['time'] ?? null;

        return $this->appointment_statuses_id == Status::getActiveId()
            && $time
            && Carbon::createFromTimestamp($time)->startOfDay()->lte(Carbon::now());
    }

    /**
     * @return bool
     */
    public function getCanRescheduleAttribute(): bool
    {
        return $this->appointment_statuses_id == Status::getActiveId();
    }

    /**
     * @return bool
     */
    public function getCanCancelAttribute(): bool
    {
        // ToDo: add strict comparison?
        return $this->appointment_statuses_id == Status::getActiveId();
    }

    public function getPatientId(): ?int
    {
        return $this->patients_id;
    }

    public function ringcentralCallLogs(): MorphMany
    {
        return $this->morphMany(RingcentralCallLog::class, 'call_subject');
    }

    public function treatmentModality(): BelongsTo
    {
        return $this->belongsTo(TreatmentModality::class, 'treatment_modality_id', 'id');
    }

    public static function scopeActiveForPeriod(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query
            ->select([
                'id',
                'patients_id',
                'providers_id',
                'appointment_statuses_id',
                'time',
            ])
            ->onlyActive()
            ->whereBetween('time', [$from->timestamp, $to->timestamp])
            ->with([
                'patient' => function ($withQuery) {
                    $withQuery->select([
                        'id',
                        'first_name',
                        'last_name',
                        'status_id',
                        'primary_insurance_id',
                        'visit_copay',
                    ]);
                },
                'patient.status' => function ($withQuery) {
                    $withQuery->select(['id', 'status', 'hex_color']);
                },
                'provider' => function ($withQuery) {
                    $withQuery->select(['id', 'provider_name'])->withTrashed();
                },
                'status' => function ($withQuery) {
                    $withQuery->select(['id', 'status']);
                },
            ])
            ->whereHas('patient', function ($patientQuery) {
                $patientQuery->where('is_test', 0);
            });
    }
    public static function scopeForPeriod(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query
            ->select([
                'id',
                'patients_id',
                'providers_id',
                'appointment_statuses_id',
                'time',
            ])
            ->whereBetween('time', [$from->timestamp, $to->timestamp])
            ->with([
                'patient' => function ($withQuery) {
                    $withQuery->select([
                        'id',
                        'first_name',
                        'last_name',
                        'status_id',
                        'primary_insurance_id',
                        'visit_copay',
                    ]);
                },
                'patient.status' => function ($withQuery) {
                    $withQuery->select(['id', 'status', 'hex_color']);
                },
                'provider' => function ($withQuery) {
                    $withQuery->select(['id', 'provider_name'])->withTrashed();
                },
                'status' => function ($withQuery) {
                    $withQuery->select(['id', 'status']);
                },
            ])
            ->whereHas('patient', function ($patientQuery) {
                $patientQuery->where('is_test', 0);
            });
    }

    public static function scopeWithPatientAppointmentsCount(Builder $query): Builder
    {
        return $query->with([
            'patient' => function ($query) {
                $query->select([
                    'id',
                    'first_name',
                    'last_name',
                    'status_id',
                    'primary_insurance_id',
                    'visit_copay',
                ])->withCount([
                    'appointments' => function ($query) {
                        $completedStatusId = Status::getCompletedId();
                        $visitCreatedStatusId = Status::getVisitCreatedId();
                        
                        $query->whereIn('appointment_statuses_id', [
                            $completedStatusId,
                            $visitCreatedStatusId
                        ]);
                    }
                ]);
            }
        ]);
    }

    public function removeMeetings() {
        $googleMeet = $this->googleMeet;
        $uphealMeet = $this->uphealMeet;

        if (isset($googleMeet)) {
            $googleMeet->invitations()->delete();
            $googleMeet->delete();
        }

        if (isset($uphealMeet)) {
            $uphealMeet->invitations()->delete();
            $uphealMeet->delete();
        }
    }

    public function getCreateLogMessage(): string
    {
        return 'Appointment created: ' . $this->getLogMessageIdentifier();
    }

    public function getUpdateLogMessage(array $dirtyFields = null): string
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

        if (isset($dirtyFields['appointment_statuses_id'])) {
            $prevStatus = Status::find($dirtyFields['appointment_statuses_id']['prev']);
            $currStatus = Status::find($dirtyFields['appointment_statuses_id']['curr']);
            $messagesList[] = "Status id changed from '" . optional($prevStatus)->id . "' to '" . optional($currStatus)->id . "'";
            $messagesList[] = "Status name changed from '" . optional($prevStatus)->status . "' to '" . optional($currStatus)->status . "'";
        }

        if (isset($dirtyFields['provider_id'])) {
            $prevProvider = Provider::find($dirtyFields['provider_id']['prev']);
            $currProvider = Provider::find($dirtyFields['provider_id']['curr']);
            $messagesList[] = "Provider id changed from '" . optional($prevProvider)->id . "' to '" . optional($currProvider)->id . "'";
            $messagesList[] = "Provider name changed from '" . optional($prevProvider)->provider_name . "' to '" . optional($currProvider)->provider_name . "'";
        }

        if (isset($dirtyFields['patients_id'])) {
            $prevPatient = Patient::find($dirtyFields['patients_id']['prev']);
            $currPatient = Patient::find($dirtyFields['patients_id']['curr']);
            $messagesList[] = "Patient id changed from '" . optional($prevPatient)->id . "' to '" . optional($currPatient)->id . "'";
            $messagesList[] = "Patient name changed from '" . optional($prevPatient)->getFullname() . "' to '" . optional($currPatient)->getFullname() . "'";
        }

        if (isset($dirtyFields['offices_id'])) {
            $prevOffice = Office::find($dirtyFields['offices_id']['prev']);
            $currOffice = Office::find($dirtyFields['offices_id']['curr']);
            $messagesList[] = "Office id changed from '" . optional($prevOffice)->id . "' to '" . optional($currOffice)->id . "'";
            $messagesList[] = "Office name changed from '" . optional($prevOffice)->office . "' to '" . optional($currOffice)->office . "'";
        }

        if (isset($dirtyFields['office_room_id'])) {
            $prevOfficeRoom = OfficeRoom::find($dirtyFields['office_room_id']['prev']);
            $currPatient = OfficeRoom::find($dirtyFields['office_room_id']['curr']);
            $messagesList[] = "Office room id changed from '" . optional($prevOfficeRoom)->id . "' to '" . optional($currPatient)->id . "'";
            $messagesList[] = "Office room name changed from '" . optional($prevOfficeRoom)->name . "' to '" . optional($currPatient)->name . "'";
        }

        return 'Appointment updated: ' . implode('; ', $messagesList);
    }

    public function getDeleteLogMessage(): string
    {
        return 'Appointment deleted: ' . $this->getLogMessageIdentifier();
    }

    public function getRestoreLogMessage(): string
    {
        return 'Appointment restored: ' . $this->getLogMessageIdentifier();
    }

    public function getDirtyWithOriginal(): array
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

    public function getLogData(): array
    {
        return [
            'id' => $this->id,
            'idAppointments' => $this->idAppointments,
            'resource' => $this->resource,
            'time' => $this->time,
            'is_initial' => $this->is_initial,
            'initial_assessment_type' => $this->initial_assessment_type,
            'initial_assessment_id' => $this->initial_assessment_id,
            'initial_assessment_created_at' => $this->initial_assessment_created_at,
            'check_in' => $this->check_in,
            'date_created' => $this->date_created,
            'visit_copay' => $this->visit_copay,
            'visit_length' => $this->visit_length,
            'notes' => $this->notes,
            'custom_notes' => $this->custom_notes,
            'reason_for_visit' => $this->reason_for_visit,
            'sheldued_by' => $this->sheldued_by,
            'patients_id' => $this->patients_id,
            'patients_name' => optional($this->patient)->getFullName(),
            'provider_id' => $this->providers_id,
            'provider_name' => optional($this->provider)->provider_name,
            'offices_id' => $this->offices_id,
            'offices_name' => optional($this->office)->office,
            'office_room_id' => $this->office_room_id,
            'office_room_name' => optional($this->officeRoom)->name,
            'appointment_statuses_id' => $this->appointment_statuses_id,
            'appointment_statuses_name' => optional($this->status)->status,
            'is_paid' => $this->is_paid,
            'is_created_by_tridiuum' => $this->is_created_by_tridiuum,
            'parsed_at' => $this->parsed_at,
            'not_found_count' => $this->not_found_count,
            'payed' => $this->payed,
            'note_on_paper' => $this->note_on_paper,
            'treatment_modality_id' => $this->treatment_modality_id,
            'start_completing_date' => $this->start_completing_date,
            'start_creating_visit' => $this->start_creating_visit,
            'new_status_id' => $this->new_status_id,
            'is_creating_visit_inprogress' => $this->is_creating_visit_inprogress,
            'is_warning' => $this->is_warning,
            'error_message' => $this->error_message,
        ];
    }

    public function getScalarChangeableFields(): array
    {
        return [
            'idAppointments' => 'Appointment office ally id',
            'resource' => 'Resource',
            'time' => 'Time',
            'is_initial' => 'Is initial',
            'initial_assessment_type' => 'Initial assessment type',
            'initial_assessment_id' => 'Initial assessment id',
            'initial_assessment_created_at' => 'Initial assessment created at',
            'check_in' => 'Check in',
            'date_created' => 'Date of creation',
            'visit_copay' => 'Copay for visit',
            'visit_length' => 'Visit length',
            'notes' => 'Notes',
            'custom_notes' => 'Custom notes',
            'reason_for_visit' => '',
            'sheldued_by' => 'Scheduled by',
            'is_paid' => 'Is paid',
            'is_created_by_tridiuum' => 'Is created by tridiuum',
            'parsed_at' => 'Parsed at',
            'not_found_count' => 'Not found count',
            'payed' => 'Payed',
            'note_on_paper' => 'Note on paper',
            'start_completing_date' => 'Start completing date',
            'start_creating_visit' => 'Start creating visit',
            'new_status_id' => 'New status id',
            'is_creating_visit_inprogress' => 'Is creating visit in progress',
            'error_message' => 'Error message',
        ];
    }

    public function getLogMessageIdentifier(): string
    {
        $providerName = optional($this->provider)->provider_name;
        $patientFullname = optional($this->patient)->getFullname();

        return "{$this->id}; {$this->idAppointments}; '{$providerName}'; '{$patientFullname};" . $this->date_created;
    }

    public function getTimeFromLogs()
    {
        $ringCentralAppointmentTime = $this->getTimeFromRingCentralCallLogs();

        if (isset($ringCentralAppointmentTime)) {
            return $ringCentralAppointmentTime;
        }

        $googleMeetAppointmentTime = $this->getTimeFromGoogleMeetCallLogs();

        return $googleMeetAppointmentTime;
    }

    private function getTimeFromRingCentralCallLogs()
    {
        $firstLog = $this->ringcentralCallLogs()->orderBy('call_starts_at', 'asc')->first();
        $lastLog = $this->ringcentralCallLogs()->orderBy('call_ends_at', 'desc')->first();

        if (!isset($firstLog) || !isset($lastLog)) {
            return null;
        }

        return [
            'start_time' => Carbon::parse($firstLog->call_starts_at)->format('g:i A'),
            'end_time' => Carbon::parse($lastLog->call_ends_at)->format('g:i A')
        ];
    }

    private function getTimeFromGoogleMeetCallLogs()
    {
        $googleMeeting = $this->googleMeet;

        if (!isset($googleMeeting)) {
            return null;
        }

        $firstLog = $googleMeeting->callLogs()->where('is_external', false)->orderBy('call_starts_at', 'asc')->first();
        $lastLog = $googleMeeting->callLogs()->where('is_external', false)->orderBy('call_ends_at', 'desc')->first();

        if (!isset($firstLog) || !isset($lastLog)) {
            return null;
        }

        return [
            'start_time' => Carbon::parse($firstLog->call_starts_at)->format('g:i A'),
            'end_time' => Carbon::parse($lastLog->call_ends_at)->format('g:i A')
        ];
    }
}

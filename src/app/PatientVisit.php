<?php

namespace App;

use App\Models\Diagnose;
use App\Models\Provider\Salary;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * App\PatientVisit
 *
 * @property int $id
 * @property int|null $visit_id
 * @property int|null $appointment_id
 * @property int|null $patient_id
 * @property int|null $provider_id
 * @property int|null $provider_tariff_plan_id
 * @property int|null $insurance_id
 * @property int|null $plan_id
 * @property int|null $procedure_id
 * @property int|null $reason_id
 * @property int|null $salary_timesheet_visit_id
 * @property int $is_paid
 * @property string $date
 * @property int|null $status_id
 * @property int $is_cash
 * @property float|null $copay
 * @property string|null $pos
 * @property int $needs_update_salary If true, this visit must be updated in salary table
 * @property int $is_update_salary_enabled
 * @property bool $is_overtime
 * @property int $from_completed_appointment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $parsed_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Appointment|null $appointment
 * @property-read \Illuminate\Database\Eloquent\Collection|Diagnose[] $diagnoses
 * @property-read int|null $diagnoses_count
 * @property-read \App\Patient|null $patient
 * @property-read \App\Provider|null $provider
 * @property-read \Illuminate\Database\Eloquent\Collection|Salary[] $salary
 * @property-read int|null $salary_count
 * @property-read \App\PatientVisitStatus|null $status
 * @method static EloquentBuilder|PatientVisit needsUpdateSalary()
 * @method static EloquentBuilder|PatientVisit newModelQuery()
 * @method static EloquentBuilder|PatientVisit newQuery()
 * @method static QueryBuilder|PatientVisit onlyTrashed()
 * @method static EloquentBuilder|PatientVisit query()
 * @method static EloquentBuilder|PatientVisit whereAppointmentId($value)
 * @method static EloquentBuilder|PatientVisit whereCopay($value)
 * @method static EloquentBuilder|PatientVisit whereCreatedAt($value)
 * @method static EloquentBuilder|PatientVisit whereDate($value)
 * @method static EloquentBuilder|PatientVisit whereDeletedAt($value)
 * @method static EloquentBuilder|PatientVisit whereFromCompletedAppointment($value)
 * @method static EloquentBuilder|PatientVisit whereId($value)
 * @method static EloquentBuilder|PatientVisit whereInsuranceId($value)
 * @method static EloquentBuilder|PatientVisit whereIsCash($value)
 * @method static EloquentBuilder|PatientVisit whereIsOvertime($value)
 * @method static EloquentBuilder|PatientVisit whereIsPaid($value)
 * @method static EloquentBuilder|PatientVisit whereIsUpdateSalaryEnabled($value)
 * @method static EloquentBuilder|PatientVisit whereNeedsUpdateSalary($value)
 * @method static EloquentBuilder|PatientVisit whereParsedAt($value)
 * @method static EloquentBuilder|PatientVisit wherePatientId($value)
 * @method static EloquentBuilder|PatientVisit wherePlanId($value)
 * @method static EloquentBuilder|PatientVisit wherePos($value)
 * @method static EloquentBuilder|PatientVisit whereProcedureId($value)
 * @method static EloquentBuilder|PatientVisit whereProviderId($value)
 * @method static EloquentBuilder|PatientVisit whereProviderTariffPlanId($value)
 * @method static EloquentBuilder|PatientVisit whereReasonId($value)
 * @method static EloquentBuilder|PatientVisit whereSalaryTimesheetVisitId($value)
 * @method static EloquentBuilder|PatientVisit whereStatusId($value)
 * @method static EloquentBuilder|PatientVisit whereUpdatedAt($value)
 * @method static EloquentBuilder|PatientVisit whereVisitId($value)
 * @method static QueryBuilder|PatientVisit withTrashed()
 * @method static QueryBuilder|PatientVisit withoutTrashed()
 * @mixin \Eloquent
 */
class PatientVisit extends Model
{
    use SoftDeletes;

    protected $table = 'patient_visits';

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    protected $hidden = [
        'needs_update_salary'
    ];

    protected $casts = [
        'is_overtime' => 'bool', 
    ];

    protected $fillable = [
        'visit_id',
        'appointment_id',
        'patient_id',
        'provider_id',
        'provider_tariff_plan_id',
        'insurance_id',
        'plan_id',
        'procedure_id',
        'reason_id',
        'is_telehealth',
        'is_paid',
        'date',
        'status_id',
        'is_cash',
        'copay',
        'pos',
        'needs_update_salary',
        'is_update_salary_enabled',
        'is_overtime',
        'salary_timesheet_visit_id',
        'parsed_at',
        'from_completed_appointment',
    ];

    private const LOG_FORBIDDEN_FIELDS = [
        'needs_update_salary',
        'updated_at',
    ];

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class,'patient_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class,'provider_id', 'id');
    }

    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class,'appointment_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(PatientVisitStatus::class,'status_id');
    }

    public function salary(): HasMany
    {
        return $this->hasMany(Salary::class, 'visit_id');
    }

    /**
     * @return BelongsToMany
     */
    public function diagnoses(): BelongsToMany
    {
        return $this->belongsToMany(Diagnose::class, 'patient_visit_diagnoses', 'visit_id');
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeNeedsUpdateSalary($query)
    {
        return $query->where($this->getTable() . '.needs_update_salary', 1);
    }

    public function getCreateLogMessage(): string
    {
        return 'Visit created: ' . $this->getLogMessageIdentifier();
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

        if (isset($dirtyFields['status_id'])) {
            $prevStatus = PatientVisitStatus::find($dirtyFields['status_id']['prev']);
            $currStatus = PatientVisitStatus::find($dirtyFields['status_id']['curr']);
            $messagesList[] = "Status id changed from '" . optional($prevStatus)->id . "' to '" . optional($currStatus)->id . "'";
            $messagesList[] = "Status name changed from '" . optional($prevStatus)->status . "' to '" . optional($currStatus)->status . "'";
        }

        if (isset($dirtyFields['provider_id'])) {
            $prevProvider = Provider::find($dirtyFields['provider_id']['prev']);
            $currProvider = Provider::find($dirtyFields['provider_id']['curr']);
            $messagesList[] = "Provider id changed from '" . optional($prevProvider)->id . "' to '" . optional($currProvider)->id . "'";
            $messagesList[] = "Provider name changed from '" . optional($prevProvider)->provider_name . "' to '" . optional($currProvider)->provider_name . "'";
        }

        if (isset($dirtyFields['patient_id'])) {
            $prevPatient = Patient::find($dirtyFields['patient_id']['prev']);
            $currPatient = Patient::find($dirtyFields['patient_id']['curr']);
            $messagesList[] = "Patient id changed from '" . optional($prevPatient)->id . "' to '" . optional($currPatient)->id . "'";
            $messagesList[] = "Patient name changed from '" . optional($prevPatient)->getFullname() . "' to '" . optional($currPatient)->getFullname() . "'";
        }

        if (isset($dirtyFields['deleted_at'])) {
            $prevDeletedAt = optional($dirtyFields['deleted_at']['prev'])->toDateTimeString();
            $currDeletedAt = optional($dirtyFields['deleted_at']['curr'])->toDateTimeString();
            $messagesList[] = "Deleted at changed from '{$prevDeletedAt}' to '{$currDeletedAt}'";
        }

        return 'Visit updated: ' . implode('; ', $messagesList);
    }

    public function getDeleteLogMessage(): string
    {
        return 'Visit deleted: ' . $this->getLogMessageIdentifier();
    }

    public function getRestoreLogMessage(): string
    {
        return 'Visit restored: ' . $this->getLogMessageIdentifier();
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
			'visit_id' => $this->visit_id,
			'appointment_id' => $this->appointment_id,
			'patient_id' => $this->patient_id,
			'patient_name' => optional($this->patient)->getFullName(),
			'provider_id' => $this->provider_id,
			'provider_name' => optional($this->provider)->provider_name,
			'provider_tariff_plan_id' => $this->provider_tariff_plan_id,
			'insurance_id' => $this->insurance_id,
			'plan_id' => $this->plan_id,
			'procedure_id' => $this->procedure_id,
			'reason_id' => $this->reason_id,
            'is_telehealth' => $this->is_telehealth,
			'salary_timesheet_visit_id' => $this->salary_timesheet_visit_id,
			'is_paid' => $this->is_paid,
			'date' => $this->date,
			'status_id' => $this->status_id,
			'status_name' => optional($this->status)->name,
			'is_cash' => $this->is_cash,
			'copay' => $this->copay,
			'pos' => $this->pos,
			'needs_update_salary' => $this->needs_update_salary,
			'is_update_salary_enabled' => $this->is_update_salary_enabled,
			'is_overtime' => $this->is_overtime,
			'parsed_at' => $this->parsed_at,
            'deleted_at' => optional($this->deleted_at)->toDateTimeString()
        ];
    }

    public function getScalarChangeableFields(): array
    {
        return [
            'visit_id' => 'Visit id',
            'appointment_id' => 'Appointment id',
            'provider_tariff_plan_id' => 'Provider tariff plan id',
            'insurance_id' => 'Insurance id',
            'plan_id' => 'Plan id',
            'procedure_id' => 'Procedure id',
            'reason_id' => 'Reason id',
            'is_telehealth' => 'Is telehealth',
            'salary_timesheet_visit_id' => 'Salary timesheet visit id',
            'is_paid' => 'Is paid',
            'date' => 'Date',
            'is_cash' => 'Is cash',
            'copay' => 'Copay',
            'pos' => 'Pos',
            'needs_update_salary' => 'Needs update salary',
            'is_update_salary_enabled' => 'Is update salary enabled',
            'is_overtime' => 'Is overtime',
            'parsed_at' => 'Parsed at',
        ];
    }

    public function getLogMessageIdentifier(): string
    {
        return "{$this->id}; {$this->appointment_id}; " . Carbon::parse($this->created_at)->toDateString();
    }
}

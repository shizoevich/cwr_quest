<?php

namespace App\Models\Patient\Inquiry;

use App\Appointment;
use App\Models\Patient\Lead\PatientLead;
use App\Patient;
use App\Status;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Patient\Inquiry\PatientInquiry
 *
 * @method static \Illuminate\Database\Eloquent\Builder|App\Models\Patient\Inquiry\PatientInquiry active()
 * @mixin \Eloquent
 *
 */
class PatientInquiry extends Model
{
    use SoftDeletes;

    const MARKETING_ACTIVITY_FOR_TRIDIUUM_PATIENTS = 'Kaiser';

    const DAYS_FOR_NEW_EPISODE = 28;

    const REASON_FOR_STAGE_CHANGE_CANCELED_APPOINTMENT = 'canceled';
    const REASON_FOR_STAGE_CHANGE_COMPLETED_APPOINTMENT = 'completed';

    protected $fillable = [
        'inquirable_id',
        'inquirable_type',
        'stage_id',
        'stage_changed_at',
        'registration_method_id',
        'source_id',
        'marketing_activity',
        'from_bdr',
        'admin_id',
        'is_returning',
        'is_archived',
        'onboarding_date',
        'onboarding_time',
        'onboarding_phone',
        'closed_at',
    ];

    protected $appends = [
        'is_patient_created',
    ];

    public function inquirable(): MorphTo
    {
        return $this->morphTo();
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'inquirable_id')
            ->where('patient_inquiries.inquirable_type', Patient::class);
    }

    public function patientLead(): BelongsTo
    {
        return $this->belongsTo(PatientLead::class, 'inquirable_id')
            ->where('patient_inquiries.inquirable_type', PatientLead::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PatientInquiryStage::class, 'stage_id', 'id');
    }

    public function registrationMethod(): BelongsTo
    {
        return $this->belongsTo(PatientInquiryRegistrationMethod::class, 'registration_method_id', 'id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(PatientInquirySource::class, 'source_id', 'id');
    }

    public static function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('closed_at')->where('is_archived', 0);
    }

    public static function scopeArchived(Builder $query): Builder
    {
        return $query->where('is_archived', 1);
    }

    public static function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('closed_at')->where('is_archived', 0);
    }

    public static function scopeWherePatientIsCreated(Builder $query): Builder
    {
        return $query->where('inquirable_type', Patient::class);
    }

    public static function scopeWherePatientIsNotCreated(Builder $query): Builder
    {
        return $query->where('inquirable_type', PatientLead::class);
    }

    public function isPatientCreated(): bool
    {
        return $this->inquirable_type === Patient::class;
    }

    public function getFirstActiveAppointment(): ?Appointment
    {
        if (!$this->isPatientCreated()) {
            return null;
        }

        return Appointment::query()
            ->where('patients_id', $this->inquirable_id)
            ->onlyActive()
            ->when($this->is_returning, function ($query) {
                $query->where('time', '>', $this->created_at->timestamp);
            })
            ->orderBy('time', 'ASC')
            ->first();
    }

    public function getFirstCompletedAppointment(): ?Appointment
    {
        if (!$this->isPatientCreated()) {
            return null;
        }

        return Appointment::query()
            ->where('patients_id', $this->inquirable_id)
            ->whereIn('appointment_statuses_id', Status::getCompletedVisitCreatedStatusesId())
            ->when($this->is_returning, function ($query) {
                $query->where('time', '>', $this->created_at->timestamp);
            })
            ->orderBy('time', 'ASC')
            ->first();
    }

    public function getCompletedAppointments()
    {
        if (!$this->isPatientCreated()) {
            return null;
        }

        return Appointment::query()
            ->where('patients_id', $this->inquirable_id)
            ->with(['status', 'provider' => function ($withQuery) {
                $withQuery->withTrashed();
            }])
            ->whereIn('appointment_statuses_id', Status::getCompletedVisitCreatedStatusesId())
            ->when($this->is_returning, function ($query) {
                $query->where('time', '>', $this->created_at->timestamp);
            })
            ->orderBy('time', 'desc')
            ->get();
    }

    public function getCompletedAppointmentsCount(): int
    {
        if (!$this->isPatientCreated()) {
            return 0;
        }
        
        return Appointment::query()
            ->where('patients_id', $this->inquirable_id)
            ->whereIn('appointment_statuses_id', Status::getCompletedVisitCreatedStatusesId())
            ->when($this->is_returning, function ($query) {
                $query->where('time', '>', $this->created_at->timestamp);
            })
            ->count();
    }

    public function getPastAppointmentProvider()
    {
        if (!$this->isPatientCreated()) {
            return null;
        }
        
        $lastCompletedAppointment = Appointment::query()
            ->where('patients_id', $this->inquirable_id)
            ->whereIn('appointment_statuses_id', Status::getCompletedVisitCreatedStatusesId())
            ->when($this->is_returning, function ($query) {
                $query->where('time', '>', $this->created_at->timestamp);
            })
            ->orderBy('time', 'DESC')
            ->first();
        if (isset($lastCompletedAppointment)) {
            return $lastCompletedAppointment->provider;
        }

        $lastAppointment = Appointment::query()
            ->where('patients_id', $this->inquirable_id)
            ->when($this->is_returning, function ($query) {
                $query->where('time', '>', $this->created_at->timestamp);
            })
            ->orderBy('time', 'DESC')
            ->first();
        if (isset($lastAppointment)) {
            return $lastAppointment->provider;
        }

        return null;
    }

    public function getIsPatientCreatedAttribute(): bool
    {
        return $this->isPatientCreated();
    }
}

<?php

namespace App\Models;

use App\Enums\Ringcentral\RingcentralCallerStatus;
use App\Enums\Ringcentral\RingcentralCallStatus;
use App\Enums\Ringcentral\RingcentralTelephonyStatus;
use App\User;
use App\Patient;
use App\Appointment;
use App\KaiserAppointment;
use App\Models\Patient\Lead\PatientLead;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\RingcentralCallLog
 *
 * @property int $id
 * @property int $user_id
 * @property int $patient_id
 * @property int|null $appointment_id Old morph column id
 * @property string|null $appointment_type Old morph column type
 * @property int|null $call_subject_id New morph column id
 * @property string|null $call_subject_type New morph column type
 * @property string $ring_central_session_id
 * @property string|null $phone_from
 * @property string|null $phone_to
 * @property int|null $telephony_status
 * @property int|null $call_status
 * @property int|null $caller_status
 * @property int|null $callee_status
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon|null $call_starts_at
 * @property \Illuminate\Support\Carbon|null $call_ends_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Model|\Eloquent $callSubject
 * @property-read mixed $appointment
 * @property-read mixed $call_status_name
 * @property-read mixed $call_status_title
 * @property-read mixed $callee_status_name
 * @property-read mixed $caller_status_name
 * @property-read mixed $duration
 * @property-read mixed $patient
 * @property-read mixed $telephony_status_name
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog newQuery()
 * @method static \Illuminate\Database\Query\Builder|RingcentralCallLog onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog whereAppointmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog whereAppointmentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog whereCallEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog whereCallStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog whereCallStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog whereCallSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog whereCallSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog whereCalleeStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog whereCallerStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog wherePhoneFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog wherePhoneTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog whereRingCentralSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog whereTelephonyStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RingcentralCallLog whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|RingcentralCallLog withTrashed()
 * @method static \Illuminate\Database\Query\Builder|RingcentralCallLog withoutTrashed()
 * @mixin \Eloquent
 */
class RingcentralCallLog extends Model
{
    use SoftDeletes;

    public const SUBJECT_TYPES = [
        'appointment' => Appointment::class,
        'tridiuum_appointment' => KaiserAppointment::class,
        'patient' => Patient::class,
        'patient_lead' => PatientLead::class,
    ];

    protected $fillable = [
        'user_id',
        'patient_id',
        'appointment_id',
        'appointment_type',
        'call_subject_id',
        'call_subject_type',
        'ring_central_session_id',
        'phone_from',
        'phone_to',
        'telephony_status',
        'call_status',
        'caller_status',
        'callee_status',
        'only_for_admin',
        'comment',
        'call_starts_at',
        'call_ends_at',
    ];
    
    protected $casts = [
        'user_id'          => 'int',
        'patient_id'       => 'int',
        'appointment_id'   => 'int',
        'call_subject_id  '=> 'int',
        'telephony_status' => 'int',
        'call_status'      => 'int',
        'caller_status'    => 'int',
        'callee_status'    => 'int',
        'only_for_admin'   => 'boolean',
    ];
    
    protected $dates = [
        'call_starts_at',
        'call_ends_at',
    ];
    
    protected $appends = [
        'telephony_status_name',
        'call_status_name',
        'caller_status_name',
        'callee_status_name',
        'call_status_title',
        'duration',
    ];
    
    public function getTelephonyStatusNameAttribute()
    {
        return RingcentralTelephonyStatus::getNameByStatus($this->telephony_status);
    }
    
    public function getCallStatusNameAttribute()
    {
        return RingcentralCallStatus::getNameByStatus($this->call_status);
    }
    
    public function getCallerStatusNameAttribute()
    {
        return RingcentralCallerStatus::getNameByStatus($this->caller_status);
    }
    
    public function getCalleeStatusNameAttribute()
    {
        return RingcentralCallerStatus::getNameByStatus($this->callee_status);
    }
    
    public function getCallStatusTitleAttribute()
    {
        return camelToTitle(RingcentralCallStatus::getNameByStatus($this->call_status));
    }
    
    public function getDurationAttribute()
    {
        if($this->call_starts_at && $this->call_ends_at) {
            return $this->call_ends_at->diffInSeconds($this->call_starts_at);
        }
        
        return null;
    }
    
    public function callSubject()
    {
        return $this->morphTo();
    }

    public function getPatientAttribute()
    {
        if (in_array($this->call_subject_type, [Patient::class])) {
            return $this->callSubject;
        }

        return null;
    }

    public function getAppointmentAttribute()
    {
        if (in_array($this->call_subject_type, [Appointment::class, KaiserAppointment::class])) {
            return $this->callSubject;
        }

        return null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

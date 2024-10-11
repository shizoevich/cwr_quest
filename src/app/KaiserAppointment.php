<?php

namespace App;

use App\Models\RingcentralCallLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use App\Contracts\Models\Appointment as AppointmentContract;
use App\Appointment;
use App\Models\TreatmentModality;
use Carbon\Carbon;

/**
 * \App\KaiserAppointment
 *
 * @property int $id
 * @property string $tridiuum_id
 * @property \Carbon\Carbon $start_date
 * @property int $duration
 * @property string $notes
 * @property string $reason
 * @property int $patient_id
 * @property string $first_name
 * @property string $last_name
 * @property string $sex
 * @property \Carbon\Carbon $date_of_birth
 * @property int $provider_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Patient $patient
 * @property-read \App\Provider $provider
 * @method static \Illuminate\Database\Eloquent\Builder|\App\KaiserAppointment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\KaiserAppointment whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\KaiserAppointment whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\KaiserAppointment whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\KaiserAppointment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\KaiserAppointment whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\KaiserAppointment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\KaiserAppointment wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\KaiserAppointment whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\KaiserAppointment whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\KaiserAppointment whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\KaiserAppointment whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\KaiserAppointment whereTridiuumId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\KaiserAppointment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class KaiserAppointment extends Model implements AppointmentContract
{
    const STATUS_CREATED = 1;
    const STATUS_CANCELED = 2;
    const MALE = 'Male';
    const FEMALE = 'Female';
    const MAX_MRN_LENGTH = 12;

    protected $fillable = [
        'internal_id',
        'tridiuum_id',
        'start_date',
        'duration',
        'notes',
        'reason',
        'patient_id',
        'mrn',
        'first_name',
        'last_name',
        'sex',
        'date_of_birth',
        'provider_id',
        'site_id',
        'status',
        'user_id',
        'comment',
        'cell_phone',
        'is_virtual',
    ];

    protected $casts = [
        'internal_id' => 'int',
        'tridiuum_id' => 'string',
        'start_date' => 'datetime',
        'duration' => 'int',
        'notes' => 'string',
        'reason' => 'string',
        'patient_id' => 'int',
        'mrn' => 'string',
        'first_name' => 'string',
        'last_name' => 'string',
        'sex' => 'string',
        'date_of_birth' => 'date',
        'provider_id' => 'int',
        'site_id' => 'int',
        'status' => 'int',
        'user_id' => 'int',
        'cell_phone' => 'string',
        'is_virtual' => 'bool',
    ];

    protected $appends = ['status_label', 'gender', 'prepared_mrn'];

    public function patient()
    {
        return $this->belongsTo('App\Patient', 'patient_id', 'id');
    }

    public function provider()
    {
        return $this->belongsTo('App\Provider', 'provider_id', 'id');
    }

    public function getStatusLabelAttribute()
    {
        if($this->status) {
            if($this->status === self::STATUS_CREATED) {
                return 'created';
            } elseif ($this->status === self::STATUS_CANCELED) {
                return 'canceled';
            }
        }

        return 'new';
    }

    public function getGenderAttribute()
    {
        if($this->sex == 'M') {
            return self::MALE;
        } elseif($this->sex == 'F') {
            return self::FEMALE;
        } else {
            return 'Other';
        }
    }

    /**
     * @return string
     */
    public function getPreparedMrnAttribute(): string
    {
        return prepare_mrn((string)$this->mrn);
    }

    public function callLogs()
    {
        return $this->morphMany(RingcentralCallLog::class, 'call_subject')->orderByDesc('id')->take(5);
    }

    public function site()
    {
        return $this->belongsTo(TridiuumSite::class, 'site_id');
    }

    public function secretary()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function newAppointmentsCount(Carbon $date = null)
    {
        if (!Schema::hasTable('kaiser_appointments')) {
            return 0;
        }

        $appointments = KaiserAppointment::query()
            ->whereNull('status')
            ->whereNotNull('provider_id')
            ->when(isset($date), function($query) use (&$date) {
                $query->whereDate('created_at', $date->toDateString());
            })
            ->get();

        return $appointments
            ->filter(function ($kaiserAppointment) {
                return !$kaiserAppointment->hasPreviousVisitCreated();
            })
            ->count();
    }

    public function getTreatmentModalityId()
    {
        if ($this->hasPreviousVisitCreated()) {
            return $this->is_virtual ?
                TreatmentModality::getTreatmentModalityIdByName(TreatmentModality::DEFAULT_TELEHEALTH_TREATMENT_MODALITY) :
                TreatmentModality::getTreatmentModalityIdByName(TreatmentModality::DEFAULT_IN_PERSON_TREATMENT_MODALITY);
        }

        return $this->is_virtual ?
            TreatmentModality::getTreatmentModalityIdByName(TreatmentModality::INITIAL_EVALUATION_TELEHEALTH_TREATMENT_MODALITY) :
            TreatmentModality::getTreatmentModalityIdByName(TreatmentModality::INITIAL_EVALUATION_TREATMENT_MODALITY);
    }

    public function hasPreviousVisitCreated()
    {
        if (empty($this->patient_id)) {
            return false;
        }

        $kaiserAppointmentDate = Carbon::parse($this->created_at)->toDateString();

        return Appointment::query()
            ->where('patients_id', $this->patient_id)
            ->whereRaw("
                DATE(DATE_FORMAT(FROM_UNIXTIME(time), '%Y-%m-%d')) < DATE('$kaiserAppointmentDate')
            ")
            ->onlyVisitCreated()
            ->exists();
    }
    
    public function getPatientId()
    {
        return $this->patient_id;
    }
}

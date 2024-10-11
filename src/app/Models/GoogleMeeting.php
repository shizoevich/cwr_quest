<?php

namespace App\Models;

use App\Appointment;
use App\Patient;
use App\Provider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\GoogleMeeting
 *
 * @property int $id
 * @property int $patient_id
 * @property int $provider_id
 * @property int|null $appointment_id
 * @property int|null $notification_id scheduled_notifications.id
 * @property string $calendar_event_external_id
 * @property string|null $conference_request_external_id
 * @property string|null $conference_external_id
 * @property string|null $conference_uri
 * @property string|null $conference_phone
 * @property string|null $conference_phone_pin
 * @property bool $allow_to_join_by_phone
 * @property int $conference_creation_status
 * @property \Illuminate\Support\Carbon|null $event_starts_at
 * @property \Illuminate\Support\Carbon|null $event_ends_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Appointment|null $appointment
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GoogleMeetingCallLog[] $callLogs
 * @property-read int|null $call_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ScheduledNotification[] $invitations
 * @property-read Patient $patient
 * @property-read Provider $provider
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeeting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeeting newQuery()
 * @method static \Illuminate\Database\Query\Builder|GoogleMeeting onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeeting query()
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeeting whereAllowToJoinByPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeeting whereAppointmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeeting whereCalendarEventExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeeting whereConferenceCreationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeeting whereConferenceExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeeting whereConferencePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeeting whereConferencePhonePin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeeting whereConferenceRequestExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeeting whereConferenceUri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeeting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeeting whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeeting whereEventEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeeting whereEventStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeeting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeeting whereNotificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeeting wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeeting whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeeting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|GoogleMeeting withTrashed()
 * @method static \Illuminate\Database\Query\Builder|GoogleMeeting withoutTrashed()
 * @mixin \Eloquent
 */
class GoogleMeeting extends Model
{
    use SoftDeletes;

    const CONFERENCE_CREATED_SUCCESS = 1;
    
    const CONFERENCE_CREATE_PENDING = 2;
    
    const CONFERENCE_CREATE_FAILED = 3;
    
    
    protected $fillable = [
        'patient_id',
        'provider_id',
        'appointment_id',
        'calendar_event_external_id',
        'conference_request_external_id',
        'conference_external_id',
        'conference_uri',
        'conference_phone',
        'conference_phone_pin',
        'conference_creation_status',
        'notification_id',
        'event_starts_at',
        'event_ends_at',
        'allow_to_join_by_phone',
    ];
    
    protected $dates = [
        'event_starts_at',
        'event_ends_at',
    ];
    
    protected $casts = [
        'patient_id'            => 'int',
        'provider_id'           => 'int',
        'appointment_id'        => 'int',
        'conference_creation_status' => 'int',
        'notification_id' => 'int',
        'allow_to_join_by_phone' => 'bool',
    ];
    
    /**
     * @return BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    
    /**
     * @return BelongsTo
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * @return BelongsTo
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * @return MorphMany
     */
    public function invitations()
    {
        return $this->morphMany(ScheduledNotification::class, 'meeting');
    }

    /**
     * @return HasMany
     */
    public function callLogs(): HasMany
    {
        return $this->hasMany(GoogleMeetingCallLog::class, 'google_meeting_id');
    }

    /**
     * @return bool
     */
    public function conferenceCreationPending()
    {
        return $this->conference_creation_status == self::CONFERENCE_CREATE_PENDING;
    }
    
    /**
     * @return bool
     */
    public function conferenceCreatedSuccess()
    {
        return $this->conference_creation_status == self::CONFERENCE_CREATED_SUCCESS;
    }
    
    /**
     * @return bool
     */
    public function conferenceCreationFailed()
    {
        return $this->conference_creation_status == self::CONFERENCE_CREATE_FAILED;
    }

    public function getDirtyWithOriginal(): array
    {
        $result = [];
        $dirtyFields = $this->getDirty();

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
            'patient_id' => $this->patient_id,
            'patient_name' => optional($this->patient)->getFullName(),
            'provider_id' => $this->patient_id,
            'provider_name' => optional($this->provider)->provider_name,
            'appointment_id' => $this->appointment_id,
            'notification_id' => $this->notification_id,
            'conference_request_external_id' => $this->conference_request_external_id,
            'conference_external_id' => $this->conference_external_id,
            'conference_phone' => $this->conference_phone,
            'conference_phone_pin' => $this->conference_phone_pin,
            'allow_to_join_by_phone' => $this->allow_to_join_by_phone,
            'conference_creation_status' => $this->conference_creation_status,
            'event_starts_at' => $this->event_starts_at->toDateTimeString(),
            'event_ends_at' => $this->event_ends_at->toDateTimeString(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function getCreateLogMessage(): string
    {
        return 'GoogleMeeting created: ' . $this->getLogMessageIdentifier();
    }

    public function getUpdateLogMessage($dirtyFields = null): string
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

        if (isset($dirtyFields['patient_id'])) {
            $prevPatient = Patient::find($dirtyFields['patient_id']['prev']);
            $currPatient = Patient::find($dirtyFields['patient_id']['curr']);
            $messagesList[] = "Patient id changed from '" . optional($prevPatient)->id . "' to '" . optional($currPatient)->id . "'";
            $messagesList[] = "Patient name changed from '" . optional($prevPatient)->getFullname() . "' to '" . optional($currPatient)->getFullname() . "'";
        }

        if (isset($dirtyFields['provider_id'])) {
            $prevProvider = Provider::find($dirtyFields['provider_id']['prev']);
            $currProvider = Provider::find($dirtyFields['provider_id']['curr']);
            $messagesList[] = "Provider id changed from '" . optional($prevProvider)->id . "' to '" . optional($currProvider)->id . "'";
            $messagesList[] = "Provider name changed from '" . optional($prevProvider)->provider_name . "' to '" . optional($currProvider)->provider_name . "'";
        }

        if (isset($dirtyFields['event_starts_at'])) {
            $prevStartsAt = optional($dirtyFields['event_starts_at']['prev'])->toDateTimeString();
            $currStartsAt = optional($dirtyFields['event_starts_at']['curr'])->toDateTimeString();
            $messagesList[] = "Starts at changed from '{$prevStartsAt}' to '{$currStartsAt}'";
        }

        if (isset($dirtyFields['event_ends_at'])) {
            $prevEndsAt = optional($dirtyFields['event_ends_at']['prev'])->toDateTimeString();
            $currEndsAt = optional($dirtyFields['event_ends_at']['curr'])->toDateTimeString();
            $messagesList[] = "Ends at changed from '{$prevEndsAt}' to '{$currEndsAt}'";
        }

        return 'GoogleMeeting updated: ' . implode('; ', $messagesList);
    }

    public function getDeleteLogMessage(): string
    {
        return 'GoogleMeeting deleted: ' . $this->getLogMessageIdentifier();
    }

    public function getLogMessageIdentifier(): string
    {
        $patientFullname = optional($this->patient)->getFullName();
        $providerFullname = optional($this->provider)->provider_name;

        return "{$this->id}; {$this->conference_external_id}; {$this->appointment_id}; {$patientFullname}; {$providerFullname};"
            . optional($this->event_starts_at)->toDateTimeString() . ' - ' . optional($this->event_ends_at)->toDateTimeString();
    }

    public function getScalarChangeableFields(): array
    {
        return [
            'appointment_id' => 'Appointment id',
            'notification_id' => 'Notification id',
            'conference_request_external_id' => 'Conference request external id',
            'conference_external_id' => 'Conference external id',
            'conference_phone' => 'Conference phone',
            'conference_phone_pin' => 'Conference phone pin',
            'allow_to_join_by_phone' => 'Allow to join by phone',
            'conference_creation_status' => 'Conference creation status',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ];
    }
}

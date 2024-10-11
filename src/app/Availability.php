<?php

namespace App;

use App\Traits\Filters\ProviderScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Office;
use App\OfficeRoom;
use Carbon\Carbon;

/**
 * \App\Availability
 *
 * @property int $id
 * @property int $provider_id
 * @property int $office_id
 * @property int $office_room_id
 * @property int $day_of_week
 * @property string $start_time
 * @property int $length
 * @property int $remaining_length
 * @property \Carbon\Carbon|null $start_date
 * @property bool $in_person
 * @property bool $virtual
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Availability[] $childDeletedEvents
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Availability[] $childEvents
 * @property-read mixed $repeat
 * @property-read \App\Office $office
 * @property-read \App\OfficeRoom $officeRoom
 * @property-read \App\Provider $provider
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereDayOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereInPerson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereInsurance($insuranceId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereProviderKaiserTypes($kaiserTypes)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereOffice($officeId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereOfficeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereOfficeRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereProvider($providerId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereProviderAgeGroups($ageGroupIdAll)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereProviderTypesOfClients($typesOfClientsIdAll)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereAvailabilityType($availabilityTypes)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereProviderEthnicities($ethnicities)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereProviderLanguages($languages)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereProviderPatientCategories($patientCategories)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereProviderRaces($races)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereProviderSpecialties($specialties)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereProviderTreatmentTypes($treatmentTypes)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereVirtual($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereVisitType($visitTypes)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability withTherapistSurvey()
 * @mixin \Eloquent
 */
class Availability extends Model implements LoggableModelInterface
{
    use SoftDeletes;
    use ProviderScope;

    protected $table = 'availabilities';
    protected $guarded = ['id'];
    protected $dates = ['start_date'];

    protected $casts = [
        'in_person' => 'bool',
        'virtual' => 'bool',
    ];

    public function provider()
    {
        return $this->belongsTo('App\Provider', 'provider_id', 'id');
    }

    public function office()
    {
        return $this->belongsTo('App\Office', 'office_id', 'id');
    }

    public function officeRoom()
    {
        return $this->belongsTo('App\OfficeRoom', 'office_room_id', 'id');
    }

    public function availabilityType()
    {
        return $this->belongsTo('App\AvailabilityType', 'availability_type_id', 'id');
    }

    public function availabilitySubtype()
    {
        return $this->belongsTo('App\AvailabilitySubtype', 'availability_subtype_id', 'id');
    }

    public function scopeWhereProvider($query, $providerId)
    {
        if ($providerId == 0) {
            return $query;
        }

        return $query->where('availabilities.provider_id', $providerId);
    }

    public function scopeWhereOffice($query, $officeId)
    {
        if ($officeId == 0) {
            return $query;
        }

        return $query->where('availabilities.office_id', '=', $officeId);
    }

    public function scopeWithTherapistSurvey($query)
    {
        return $query->join('users', 'availabilities.provider_id', '=', 'users.provider_id')
            ->join('therapist_survey', 'users.id', '=', 'therapist_survey.user_id');
    }

    public function scopeWhereInsurance($query, $insuranceId)
    {
        if ($insuranceId == 0) {
            return $query;
        } 

        return $query->whereHas('provider', function ($providerQuery) use ($insuranceId) {
            $providerQuery->whereHas('user', function ($userQuery) use ($insuranceId) {
                $userQuery->whereHas('therapistSurvey', function ($therapistSurveyQuery) use ($insuranceId) {
                    $therapistSurveyQuery->whereHas('insurances', function ($insurancesQuery) use ($insuranceId) {
                        $insurancesQuery->where('insurance_id', $insuranceId);
                    });
                });
            });
        });
    }

    public function scopeWhereAvailabilityTypes($query, $availabilityTypes)
    {
        if ($availabilityTypes == 0) {
            return $query;
        }

        return $query->whereIn('availabilities.availability_type_id', $availabilityTypes);
    }

    public function scopeWhereAvailabilitySubtypes($query, $availabilitySubtypes)
    {
        if ($availabilitySubtypes == 0) {
            return $query;
        }

        return $query->where(function ($subQuery) use (&$availabilitySubtypes) {
            $subQuery
                ->whereNull('availabilities.availability_subtype_id')
                ->orWhereIn('availabilities.availability_subtype_id', $availabilitySubtypes);
        });
    }

    public function scopeWhereProviderKaiserTypes($query, $kaiserTypes)
    {
        if (!empty($kaiserTypes)) {
            if (in_array(Provider::KAISER_TYPE_ACTIVE, $kaiserTypes)) {
                return $query->whereHas('provider', function ($providerQuery) {
                    $providerQuery->has('tridiuumProvider');
                });
            }
            if (in_array(Provider::KAISER_TYPE_INACTIVE, $kaiserTypes)) {
                return $query->whereHas('provider', function ($providerQuery) {
                    $providerQuery->doesntHave('tridiuumProvider');
                });
            }
        }

        return $query;
    }

    public function getRepeatAttribute()
    {
        return 0;
    }

    public function getDirtyWithOriginal()
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

    public function getLogData()
    {
        return [
            'id' => $this->id,
            'provider_id' => $this->provider_id,
            'provider_name' => $this->provider->provider_name,
            'office_id' => $this->office_id,
            'office_name' => $this->office->office,
            'office_room_id' => $this->office_room_id,
            'office_room_name' => $this->officeRoom->name,
            'day_of_week' => $this->day_of_week,
            'start_time' => $this->start_time,
            'length' => $this->length,
            'remaining_length' => $this->remaining_length,
            'start_date' => $this->start_date->toDateTimeString(),
            'in_person' => $this->in_person,
            'virtual' => $this->virtual,
            'availability_type_id' => $this->availability_type_id,
            'availability_subtype_id' => $this->availability_subtype_id,
            'comment' => $this->comment,
        ];
    }

    public function getCreateLogMessage()
    {
        return 'Availability created: ' . $this->getLogMessageIdentifier();
    }

    public function getUpdateLogMessage($dirtyFields = null)
    {
        if (!isset($dirtyFields)) {
            $dirtyFields = $this->getDirtyWithOriginal();
        }

        $messagesList = [];

        foreach ($this->getScalarChangeableFields() as $fieldName => $message) {
            if (isset($dirtyFields[$fieldName])) {
                $messagesList[] = "$message changed from '{$dirtyFields[$fieldName]['prev']}' to {$dirtyFields[$fieldName]['curr']}";
            }
        }

        if (isset($dirtyFields['provider_id'])) {
            $prevProvider = Provider::find($dirtyFields['provider_id']['prev']);
            $currProvider = Provider::find($dirtyFields['provider_id']['curr']);
            $messagesList[] = "Provider id changed from '{$prevProvider->id}' to '{$currProvider->id}'";
            $messagesList[] = "Provider name changed from '{$prevProvider->provider_name}' to '{$currProvider->provider_name}'";
        }

        if (isset($dirtyFields['office_id'])) {
            $prevOffice = Office::find($dirtyFields['office_id']['prev'])->office;
            $currOffice = Office::find($dirtyFields['office_id']['curr'])->office;
            $messagesList[] = "Office changed from '{$prevOffice}' to '{$currOffice}'";
        }

        if (isset($dirtyFields['office_room_id'])) {
            $prevOfficeRoom = OfficeRoom::find($dirtyFields['office_room_id']['prev'])->name;
            $currOfficeRoom = OfficeRoom::find($dirtyFields['office_room_id']['curr'])->name;
            $messagesList[] = "Office room changed from '{$prevOfficeRoom}' to '{$currOfficeRoom}'";
        }

        if (
            isset($dirtyFields['start_date'])
            && Carbon::parse($dirtyFields['start_date']['prev']) !== Carbon::parse($dirtyFields['start_date']['curr'])
        ) {
            $messagesList[] = "Start date changed from '{$dirtyFields['start_date']['prev']}' to '{$dirtyFields['start_date']['curr']}'";
        }

        return 'Availability updated: ' . implode('; ', $messagesList);
    }

    public function getDeleteLogMessage()
    {
        return 'Availability deleted: ' . $this->getLogMessageIdentifier();
    }

    public function getLogMessageIdentifier()
    {
        $startDate = Carbon::parse($this->start_date);
        $startDate->setTimeFromTimeString($this->start_time);
        $endDate = $startDate->copy()->addMinutes($this->length);

        return $startDate->toDateTimeString()
            . ' - '
            . $endDate->toDateTimeString()
            . '; '
            . $this->office->office
            . '; '
            . $this->officeRoom->name;
    }

    public function getScalarChangeableFields()
    {
        return [
            'day_of_week' => 'Day of week',
            'start_time' => 'Start time',
            'length' => 'Length',
            'remaining_length' => 'Remaining length',
            'in_person' => 'In person',
            'virtual' => 'Virtual'
        ];
    }
}

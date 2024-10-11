<?php

namespace App;

use App\Traits\Filters\ProviderScope;
use Illuminate\Database\Eloquent\Model;

/**
 *
 * Class is deprecated
 *
 * App\ProviderWorkHour
 *
 * @property int $id
 * @property int $provider_id
 * @property int $office_id
 * @property int|null $office_room_id
 * @property int $day_of_week
 * @property string $start_time
 * @property int $length
 * @property \Carbon\Carbon|null $start_date
 * @property \Carbon\Carbon|null $end_date
 * @property int $repeat
 * @property int|null $parent_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ProviderWorkHour[] $childDeletedEvents
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ProviderWorkHour[] $childEvents
 * @property-read \App\Office $office
 * @property-read \App\OfficeRoom|null $officeRoom
 * @property-read \App\Provider $provider
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour whereDayOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour whereInsurance($insuranceId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour whereLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour whereOffice($officeId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour whereOfficeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour whereOfficeRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour whereProvider($providerId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour whereProviderAgeGroups($ageGroupIdAll)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour whereProviderFocus($focusIdAll)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour whereProviderLanguage($languages)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour whereProviderTypesOfClients($typesOfClientsIdAll)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour whereRepeat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderWorkHour withTherapistSurvey()
 * @mixin \Eloquent
 */
class ProviderWorkHour extends Model
{
    use ProviderScope;

    protected $table = 'provider_work_hours';

    protected $guarded = [];

    protected $dates = ['start_date', 'end_date'];


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

    public function childEvents() {
        return $this->hasMany(static::class, 'parent_id', 'id')
            ->whereNull('provider_work_hours.deleted_at');
    }

    public function childDeletedEvents() {
        return $this->hasMany(static::class, 'parent_id', 'id')
            ->whereNotNull('provider_work_hours.deleted_at');
    }

    public function scopeWhereProvider($query, $providerId)
    {
        if($providerId == 0) {
            return $query;
        } else {
            return $query->where('provider_work_hours.provider_id', $providerId);
        }
    }

    public function scopeWhereOffice($query, $officeId)
    {
        if($officeId == 0) {
            return $query;
        } else {
            return $query->where('provider_work_hours.office_id', '=', $officeId);
        }
    }

    public function scopeWithTherapistSurvey($query)
    {
        return $query->join('users', 'provider_work_hours.provider_id', '=', 'users.provider_id')
            ->join('therapist_survey', 'users.id', '=', 'therapist_survey.user_id');
    }

    public function scopeWhereInsurance($query, $insuranceId)
    {
        if($insuranceId == 0) {
            return $query;
        } else {

            return $query->join('provider_insurances', function ($join) use ($insuranceId) {
                $join->on('provider_work_hours.provider_id', '=', 'provider_insurances.provider_id')
                    ->where('provider_insurances.insurance_id', $insuranceId);
            });
        }
    }

}

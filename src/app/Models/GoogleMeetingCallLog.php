<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\GoogleMeetingCallLog
 *
 * @property int $id
 * @property int $google_meeting_id
 * @property int|null $provider_id
 * @property string $external_id
 * @property int $duration Call duration in seconds
 * @property bool $is_external
 * @property string|null $caller_name
 * @property string|null $ip
 * @property bool $is_initial
 * @property \Illuminate\Support\Carbon|null $call_starts_at
 * @property \Illuminate\Support\Carbon|null $call_ends_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeetingCallLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeetingCallLog newQuery()
 * @method static \Illuminate\Database\Query\Builder|GoogleMeetingCallLog onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeetingCallLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeetingCallLog whereCallEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeetingCallLog whereCallStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeetingCallLog whereCallerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeetingCallLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeetingCallLog whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeetingCallLog whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeetingCallLog whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeetingCallLog whereGoogleMeetingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeetingCallLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeetingCallLog whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeetingCallLog whereIsExternal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeetingCallLog whereIsInitial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeetingCallLog whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleMeetingCallLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|GoogleMeetingCallLog withTrashed()
 * @method static \Illuminate\Database\Query\Builder|GoogleMeetingCallLog withoutTrashed()
 * @mixin \Eloquent
 */
class GoogleMeetingCallLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'google_meeting_id',
        'provider_id',
        'external_id',
        'duration',
        'is_external',
        'ip',
        'is_initial',
        'caller_name',
        'call_starts_at',
        'call_ends_at',
    ];
    
    protected $casts = [
        'google_meeting_id' => 'int',
        'provider_id' => 'int',
        'duration' => 'int',
        'is_external' => 'bool',
        'is_initial' => 'bool',
    ];
    
    protected $dates = [
        'call_starts_at',
        'call_ends_at',
    ];
}

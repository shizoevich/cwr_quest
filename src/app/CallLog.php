<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CallLog extends Model
{
    protected $table = 'call_logs';

    const INIT_STATUS = 'InProgress';

    const REASONS = [
        'Fail' => [
            'Accepted',
            'Missed',
            'Busy',
            'No Answer',
            'Rejected',
            'Blocked',
            'Suspended account',
            'Call Failed',
            'Call Failure',
            'Internal Error',
            'IP Phone Offline',
            'No Calling Credit',
            'Restricted Number',
            'Wrong Number',
            'Answered Not Accepted',
            'Stopped',
            'International Disabled',
            'International Restricted',
            'Abandoned',
            'Declined',
        ],
        'InProgress' => [],
        'Success' => [
            'Accepted',
            'Call connected',
            'Voicemail',
            'Hang Up',
            'Reply',
            'Received',
        ]
    ];

    const STATUSES = [
        'Fail' => [
            'Invalid', 'Busy', 'NoAnswer', 'Rejected', 'GenericError', 'DestinationBlocked', 'NotEnoughFunds', 'NoSuchUser'
        ],
        'InProgress' => [
            'InProgress'
        ],
        'Success' => [
            'Success', 'Finished'
        ]
    ];


    protected $fillable = [
        'user_id',
        'patient_id',
        'appointment_id',
        'ring_central_call_id',
        'ring_central_session_id',
        'phone_from',
        'duration',
        'phone_to',
        'duration',
        'comment',
        'status_text',
        'result',
        'reason',
        'reason_description',
    ];

    protected $casts = [
        'user_id' => 'int',
        'patient_id' => 'int',
        'appointment_id' => 'int',
        'ring_central_call_id' => 'string',
        'ring_central_session_id' => 'string',
        'phone_from' => 'string',
        'phone_to' => 'string',
        'duration' => 'integer',
        'comment' => 'string',
        'status_text' => 'string',
        'result' => 'string',
        'reason' => 'string',
        'reason_description' => 'string',
    ];

    protected $appends = ['status_title'];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function patient()
    {
        return $this->belongsTo('App\Patient', 'patient_id', 'id');
    }

    public function provider()
    {
        return $this->belongsTo('App\Provider', 'provider_id', 'id');
    }

    public function appointment()
    {
        return $this->belongsTo('App\KaiserAppointment', 'appointment_id', 'id');
    }

    public function getStatusAttribute()
    {
        self::statusId($this->result);
    }

    public function getStatusTitleAttribute()
    {
        return camelToTitle($this->status_text);
    }

    

    
    public static function statusId($status) 
    {
        if(in_array($status, static::REASONS['Fail'])) {
            return 1;
        } elseif(in_array($status, static::REASONS['Success'])) {
            return 3;
        } else {
            return 2;
        }
    }

    public static function statusText($statusText)
    {
        if(in_array($statusText, static::STATUSES['Fail'])) {
            return 1;
        } elseif(in_array($statusText, static::STATUSES['Success'])) {
            return 3;
        } else {
            return 2;
        }
    }
}

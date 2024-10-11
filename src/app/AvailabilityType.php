<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AvailabilityType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'type',
        'hex_color'
    ];

    public static function getForNewPatientsId()
    {
        return \Cache::rememberForever('availability_type:for_new_patients', function () {
            return static::where('type', 'Regular')->first()['id'];
        });
    }

    public static function getForRescheduleId()
    {
        return \Cache::rememberForever('availability_type:for_reschedule', function () {
            return static::where('type', 'Additional')->first()['id'];
        });
    }
}

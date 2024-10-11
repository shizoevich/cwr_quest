<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AvailabilitySubtype extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'type'
    ];

    public static function getIdByTypeRescheduling()
    {
        return self::where('type', 'Rescheduling only')->first()->id;
    }

    public static function getIdByTypeOther()
    {
        return self::where('type', 'Other')->first()->id;
    }

    public static function getIdByTypeUnavailable()
    {
        return self::where('type', 'Unavailable')->first()->id;
    }
}

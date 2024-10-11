<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentRescheduleSubStatus extends Model
{
    protected $fillable = [
        'status',
    ];

    public static function getAllIds()
    {
        return self::all()->pluck('id')->toArray();
    }
}

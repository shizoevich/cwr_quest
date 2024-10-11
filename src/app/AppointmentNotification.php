<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AppointmentNotification extends Model
{
    const STATUS_NEW = 1;
    const STATUS_CONFIRMED = 10;

    const TYPE_NEW_PATIENT = 1;

    protected $table = 'appointment_notifications';

    protected $fillable = [
        'provider_id',
        'appointment_id',
        'type',
        'status',
    ];

    protected $casts = [
        'provider_id' => 'integer',
        'appointment_id' => 'integer',
        'type' => 'integer',
        'status' => 'integer',
    ];

    public function appointment() {
        return $this->belongsTo('App\Appointment', 'appointment_id', 'id');
    }
}

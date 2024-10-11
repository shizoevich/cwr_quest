<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentReminderStatus extends Model
{
    protected $table = 'appointment_reminder_statuses';

    protected $fillable = [
        'status'
    ];
}

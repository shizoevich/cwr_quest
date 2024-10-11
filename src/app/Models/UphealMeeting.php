<?php

namespace App\Models;

use App\Appointment;
use App\Patient;
use App\Provider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UphealMeeting extends Model
{
    protected $fillable = [
        'patient_id',
        'provider_id',
        'appointment_id',
        'notification_id',
    ];
    
    protected $casts = [
        'patient_id' => 'int',
        'provider_id' => 'int',
        'appointment_id' => 'int',
        'notification_id' => 'int',
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
}

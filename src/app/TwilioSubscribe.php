<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\TwilioSubscribe
 *
 * @property int $id
 * @property int $patient_id
 * @property string $phone
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Patient $patient
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioSubscribe whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioSubscribe whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioSubscribe wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioSubscribe wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioSubscribe whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioSubscribe whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TwilioSubscribe extends Model
{
    public const SUBSCRIBE = 'subscribe';
    
    public const UNSUBSCRIBE = 'unsubscribe';

    protected $fillable = [
        'patient_id',
        'phone',
        'status',
    ];

    protected $table = 'patient_twilio_subscribers';

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}

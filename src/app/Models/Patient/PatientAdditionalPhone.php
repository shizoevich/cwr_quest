<?php

namespace App\Models\Patient;

use App\Patient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientAdditionalPhone extends Model
{
    public const PHONE_TYPE_CELL_PHONE = 'cell_phone';
    public const PHONE_TYPE_HOME_PHONE = 'home_phone';
    public const PHONE_TYPE_WORK_PHONE = 'work_phone';

    public const PHONE_TYPES = [
        self::PHONE_TYPE_CELL_PHONE,
        self::PHONE_TYPE_HOME_PHONE,
        self::PHONE_TYPE_WORK_PHONE,
    ];

    protected $fillable = [
        'patient_id',
        'phone',
        'label',
        'phone_type',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}

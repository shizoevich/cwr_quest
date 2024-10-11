<?php

namespace App\Models;

use App\Patient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UpcomingReauthorizationRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_id',
        'episode_start_date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }
}

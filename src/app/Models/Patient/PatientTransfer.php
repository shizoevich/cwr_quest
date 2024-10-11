<?php

namespace App\Models\Patient;

use App\Patient;
use App\Provider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientTransfer extends Model
{
    protected $fillable = [
        'patient_id',
        'old_provider_id',
        'created_by',
        'closed_at',
        'unassigned_at',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    public function oldProvider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'old_provider_id', 'id')->withTrashed();
    }

    public function close(): bool
    {
        return $this->update(['closed_at' => now()]);
    }

    public static function scopeActive($query)
    {
        return $query->whereNull('closed_at');
    }

    public static function scopeClosed($query)
    {
        return $query->whereNotNull('closed_at');
    }
}

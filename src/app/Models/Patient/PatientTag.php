<?php

namespace App\Models\Patient;

use App\Patient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class PatientTag extends Model
{
    use SoftDeletes;

    const PATIENT_TAG_TRANSFERRING = 'Transferring';
    const PATIENT_TAG_RETURNING = 'Returning';

    protected $fillable = [
        'tag',
        'hex_text_color',
        'hex_background_color',
        'is_system',
        'created_by',
    ];

    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(Patient::class, 'patient_has_tags', 'tag_id','patient_id');
    }

    public static function getTransferringId()
    {
        return Cache::rememberForever('patient_tags:transferring_id', function () {
            return self::where('tag', self::PATIENT_TAG_TRANSFERRING)->first()->id;
        });
    }

    public static function getReturningId(): int
    {
        return Cache::rememberForever('patient_tags:returning_id', function () {
            return self::where('tag', self::PATIENT_TAG_RETURNING)->first()->id;
        });
    }
}

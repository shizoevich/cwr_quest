<?php

namespace App\Models;

use App\Patient;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TridiuumPatient
 *
 * @property int                    $id
 * @property string                 $external_id
 * @property int|null               $internal_id
 * @property string|null            $mrn
 * @property string                 $first_name
 * @property string                 $last_name
 * @property string|null            $middle_initial
 * @property string|null            $email
 * @property \Carbon\Carbon|null    $date_of_birth
 * @property \Carbon\Carbon|null    $current_track_updated_at
 * @property \Carbon\Carbon|null    $parsed_at
 * @property \Carbon\Carbon|null    $created_at
 * @property \Carbon\Carbon|null    $updated_at
 * @property-read \App\Patient|null $patient
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TridiuumPatient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TridiuumPatient whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TridiuumPatient whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TridiuumPatient whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TridiuumPatient whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TridiuumPatient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TridiuumPatient whereInternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TridiuumPatient whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TridiuumPatient whereMiddleInitial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TridiuumPatient whereMrn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TridiuumPatient whereParsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TridiuumPatient whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TridiuumPatient extends Model
{
    protected $fillable = [
        'external_id',
        'internal_id',
        'mrn',
        'first_name',
        'last_name',
        'middle_initial',
        'email',
        'date_of_birth',
        'is_active',
        'current_track',
        'current_track_updated_at',
        'parsed_at',
    ];

    protected $dates = [
        'date_of_birth',
        'current_track_updated_at',
        'parsed_at',
    ];

    protected $casts = [
        'is_active'   => 'bool',
        'internal_id' => 'int',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'internal_id', 'id');
    }
}

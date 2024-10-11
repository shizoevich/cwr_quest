<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\PatientVisitStatus
 *
 * @property int $id
 * @property string $name
 * @property-read Collection|PatientVisit[] $visit
 * @method static Builder|PatientVisitStatus whereId($value)
 * @method static Builder|PatientVisitStatus whereName($value)
 * @mixin \Eloquent
 */
class PatientVisitStatus extends Model
{
    protected $table = 'patient_visit_statuses';

    protected $guarded = [];

    public $timestamps = false;

    protected $fillable = [
        'name'
    ];

    public function visit(): HasMany
    {
        return $this->hasMany(PatientVisit::class,'status_id');
    }
}

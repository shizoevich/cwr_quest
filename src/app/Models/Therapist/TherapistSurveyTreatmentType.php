<?php

namespace App\Models\Therapist;

use App\TherapistSurvey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Therapist\TherapistSurveyTreatmentType
 *
 * @property int $id
 * @property string $label
 * @property string $tridiuum_value
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\TherapistSurvey[] $therapists
 */
class TherapistSurveyTreatmentType extends Model
{
    protected $table = 'therapist_survey_treatment_types';

    public function therapists(): BelongsToMany
    {
        return $this->belongsToMany(TherapistSurvey::class, 'therapist_has_treatment_types','treatment_type_id', 'therapist_id');
    }
}

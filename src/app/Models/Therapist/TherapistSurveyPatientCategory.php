<?php

namespace App\Models\Therapist;

use App\TherapistSurvey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Therapist\TherapistSurveyCategory
 *
 * @property int $id
 * @property string $label
 * @property string $tridiuum_value
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\TherapistSurvey[] $therapists
 */
class TherapistSurveyPatientCategory extends Model
{
    protected $table = 'therapist_survey_patient_categories';

    public function therapists(): BelongsToMany
    {
        return $this->belongsToMany(TherapistSurvey::class, 'therapist_has_patient_categories','patient_category_id', 'therapist_id');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\TherapistSurveyPracticeFocusFirst
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\TherapistSurvey[] $therapists
 * @mixin \Eloquent
 */
class TherapistSurveyPracticeFocusFirst extends Model
{
    protected $table = 'therapist_survey_practice_focus_first';

    public function therapists()
    {
        return $this->belongsToMany(TherapistSurvey::class, 'therapist_has_focus_first','focus_first_id', 'therapist_id');
    }
}

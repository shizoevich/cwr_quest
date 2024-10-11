<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\TherapistSurveyPracticeFocus
 *
 * @property int $id
 * @property string $label
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\TherapistSurvey[] $therapists
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TherapistSurveyPracticeFocus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TherapistSurveyPracticeFocus whereLabel($value)
 * @mixin \Eloquent
 */
class TherapistSurveyPracticeFocus extends Model
{
    protected $table = 'therapist_survey_practice_focus';

    public function therapists()
    {
        return $this->belongsToMany(TherapistSurvey::class, 'therapist_has_focus','focus_id', 'therapist_id');
    }
}

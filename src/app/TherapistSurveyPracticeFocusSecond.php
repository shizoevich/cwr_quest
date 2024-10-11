<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\TherapistSurveyPracticeFocusSecond
 *
 * @property int $id
 * @property string $label
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\TherapistSurvey[] $therapists
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TherapistSurveyPracticeFocusSecond whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TherapistSurveyPracticeFocusSecond whereLabel($value)
 * @mixin \Eloquent
 */
class TherapistSurveyPracticeFocusSecond extends Model
{
    protected $table = 'therapist_survey_practice_focus_second';

    public function therapists()
    {
        return $this->belongsToMany(TherapistSurvey::class, 'therapist_has_focus_second','focus_second_id', 'therapist_id');
    }
}

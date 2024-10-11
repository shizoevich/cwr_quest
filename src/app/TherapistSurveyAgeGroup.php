<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\TherapistSurveyAgeGroup
 *
 * @property int $id
 * @property string $label
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\TherapistSurvey[] $therapists
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TherapistSurveyAgeGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TherapistSurveyAgeGroup whereLabel($value)
 * @mixin \Eloquent
 */
class TherapistSurveyAgeGroup extends Model
{
    protected $table = 'therapist_survey_age_groups';

    public function therapists()
    {
        return $this->belongsToMany(TherapistSurvey::class, 'therapist_has_age_group', 'age_group_id','therapist_id');
    }
}

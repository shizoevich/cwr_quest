<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\TherapistSurveyTypeOfClient
 *
 * @property int $id
 * @property string $label
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\TherapistSurvey[] $therapists
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TherapistSurveyTypeOfClient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TherapistSurveyTypeOfClient whereLabel($value)
 * @mixin \Eloquent
 */
class TherapistSurveyTypeOfClient extends Model
{
    protected $table = 'therapist_survey_type_of_clients';

    public function therapists()
    {
        return $this->belongsToMany(TherapistSurvey::class, 'therapist_has_client_type', 'client_type_id', 'therapist_id');
    }
}

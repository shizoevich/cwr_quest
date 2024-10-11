<?php

namespace App;

use App\Models\Therapist\TherapistSurveyPatientCategory;
use App\Models\Therapist\TherapistSurveyEthnicity;
use App\Models\Therapist\TherapistSurveyLanguage;
use App\Models\Therapist\TherapistSurveyModality;
use App\Models\Therapist\TherapistSurveyRace;
use App\Models\Therapist\TherapistSurveySpecialty;
use App\Models\Therapist\TherapistSurveyTreatmentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * App\TherapistSurvey
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $first_name
 * @property string|null $middle_name
 * @property string $last_name
 * @property string|null $personal_email
 * @property string|null $credentials
 * @property string|null $school
 * @property \Illuminate\Support\Carbon|null $complete_education
 * @property int|null $years_of_practice
 * @property string|null $languages
 * @property int $is_supervisor
 * @property string|null $help_description
 * @property string|null $tridiuum_external_url
 * @property int|null $group_npi
 * @property int $is_accept_video_appointments
 * @property string|null $bio
 * @property int|null $late_cancelation_fee
 * @property string|null $original_photo_name
 * @property string|null $aws_photo_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\TherapistSurveyAgeGroup[] $ageGroups
 * @property-read int|null $age_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection|TherapistSurveyEthnicity[] $ethnicities
 * @property-read int|null $ethnicities_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientInsurance[] $insurances
 * @property-read int|null $insurances_count
 * @property-read \Illuminate\Database\Eloquent\Collection|TherapistSurveyLanguage[] $languagesTridiuum
 * @property-read int|null $languages_tridiuum_count
 * @property-read \Illuminate\Database\Eloquent\Collection|TherapistSurveyPatientCategory[] $patientCategories
 * @property-read int|null $patient_categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\TherapistSurveyPracticeFocus[] $practiceFocus
 * @property-read int|null $practice_focus_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\TherapistSurveyPracticeFocusSecond[] $practiceFocusSecond
 * @property-read int|null $practice_focus_second_count
 * @property-read \Illuminate\Database\Eloquent\Collection|TherapistSurveyRace[] $races
 * @property-read int|null $races_count
 * @property-read \Illuminate\Database\Eloquent\Collection|TherapistSurveySpecialty[] $specialties
 * @property-read int|null $specialties_count
 * @property-read \Illuminate\Database\Eloquent\Collection|TherapistSurveyTreatmentType[] $treatmentTypes
 * @property-read int|null $treatment_types_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\TherapistSurveyTypeOfClient[] $typesOfClients
 * @property-read int|null $types_of_clients_count
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey query()
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey whereAwsPhotoName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey whereCompleteEducation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey whereCredentials($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey whereGroupNpi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey whereHelpDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey whereIsAcceptVideoAppointments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey whereIsSupervisor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey whereLanguages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey whereLateCancelationFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey whereMiddleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey whereOriginalPhotoName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey wherePersonalEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey whereSchool($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey whereTridiuumExternalUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TherapistSurvey whereYearsOfPractice($value)
 * @mixin \Eloquent
 */
class TherapistSurvey extends Model
{
    const FIRST_NAME ='57626270';
    const LAST_NAME = '57626274';
    const MIDDLE_NAME = '57632300';
    const CREDENTIALS = '57626343';
    const SCHOOL = '57626856';
    const COMPLETE_EDUCATION = '57629763';
    const YEARS_OF_PRACTICE = '57627018';
    const LANGUAGES = '57640291';
    const HELP_DESCRIPTION = '57629552';
    const PRACTICE_FOCUS_FIRST = '57625534';
    const PRACTICE_FOCUS_SECOND = '57626635';
    const AGE_GROUPS = '57639567';
    const TYPES_OF_CLIENTS = '57640115';

    protected $table = 'therapist_survey';

    protected $fillable = [
        'first_name',
        'middle_name',
        'personal_email',
        'last_name',
        'credentials',
        'school',
        'complete_education',
        'years_of_practice',
        'languages',
        'help_description',
        'bio',
        'is_accept_video_appointments',
        'group_npi',
        'tridiuum_external_url',
        'late_cancelation_fee',
        'original_photo_name',
        'aws_photo_name',
    ];

    protected $dates = [
      'complete_education'
    ];

    public function ageGroups()
    {
        return $this->belongsToMany(TherapistSurveyAgeGroup::class, 'therapist_has_age_group', 'therapist_id','age_group_id');
    }

    public function typesOfClients()
    {
        return $this->belongsToMany(TherapistSurveyTypeOfClient::class, 'therapist_has_client_type', 'therapist_id','client_type_id');
    }

    public function practiceFocus()
    {
        return $this->belongsToMany(TherapistSurveyPracticeFocus::class, 'therapist_has_focus', 'therapist_id','focus_id');
    }

    public function practiceFocusSecond()
    {
        return $this->belongsToMany(TherapistSurveyPracticeFocusSecond::class, 'therapist_has_focus_second', 'therapist_id','focus_second_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id','user_id');
    }

    public function patientCategories(): BelongsToMany
    {
        return $this->belongsToMany(TherapistSurveyPatientCategory::class, 'therapist_has_patient_categories', 'therapist_id','patient_category_id');
    }

    public function ethnicities(): BelongsToMany
    {
        return $this->belongsToMany(TherapistSurveyEthnicity::class, 'therapist_has_ethnicities', 'therapist_id','ethnicity_id');
    }

    public function languagesTridiuum(): BelongsToMany
    {
        return $this->belongsToMany(TherapistSurveyLanguage::class, 'therapist_has_languages', 'therapist_id','language_id');
    }
    public function races(): BelongsToMany
    {
        return $this->belongsToMany(TherapistSurveyRace::class, 'therapist_has_races', 'therapist_id','race_id');
    }

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(TherapistSurveySpecialty::class, 'therapist_has_specialties', 'therapist_id','specialty_id');
    }

    public function treatmentTypes(): BelongsToMany
    {
        return $this->belongsToMany(TherapistSurveyTreatmentType::class, 'therapist_has_treatment_types', 'therapist_id','treatment_type_id');
    }

    public function insurances(): BelongsToMany
    {
        return $this->belongsToMany(PatientInsurance::class, 'therapist_has_insurances', 'therapist_id','insurance_id');
    }

    /**
     * Returns user full name (Firstname + Lastname)
     * @return null | string
     */
    public function getFullname()
    {
        if(empty($this->first_name) && empty($this->last_name)) {
            return null;
        }
        return $this->first_name . " " . $this->last_name;
    }

    public function getLateCancelationFeeAttribute($value)
    {
        return money_round($value / 100);
    }

    public function setLateCancelationFeeAttribute($value)
    {
        $this->attributes['late_cancelation_fee'] = intval(floatval($value) * 100);
    }

    public function getPhotoTemporaryUrl()
    {
        if (empty($this->aws_photo_name)) {
            return null;
        }

        if (config('filesystems.files_storage') === 's3') {
            return Storage::disk('therapists_photos')->temporaryUrl($this->aws_photo_name, Carbon::now()->addMinutes(10));
        }

        return Storage::disk('therapists_photos')->url($this->aws_photo_name);
    }
}

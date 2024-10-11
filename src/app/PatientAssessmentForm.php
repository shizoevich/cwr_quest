<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PatientAssessmentForm
 *
 * @property int $id
 * @property int $patient_id
 * @property int $assessment_form_id
 * @property string $file_link
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string $file_nextcloud_path
 * @property int $s3_file_id
 * @property int $status
 * @property int $nextcloud_id
 * @property int $has_signature
 * @property int $signed
 * @property int $creator_id
 * @property \Carbon\Carbon|null $deleted_at
 * @property string|null $start_editing_date
 * @property-read \App\AssessmentForm $assessmentFormTemplate
 * @property-read \App\Patient $patient
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAssessmentForm initialAssessment()
 * @method static \Illuminate\Database\Query\Builder|\App\PatientAssessmentForm onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAssessmentForm whereAssessmentFormId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAssessmentForm whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAssessmentForm whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAssessmentForm whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAssessmentForm whereFileLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAssessmentForm whereFileNextcloudPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAssessmentForm whereHasSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAssessmentForm whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAssessmentForm whereNextcloudId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAssessmentForm wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAssessmentForm whereS3FileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAssessmentForm whereSigned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAssessmentForm whereStartEditingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAssessmentForm whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAssessmentForm whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\PatientAssessmentForm withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\PatientAssessmentForm withoutTrashed()
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientAssessmentForm discharge()
 */
class PatientAssessmentForm extends Model
{

    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'patients_assessment_forms';

    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    protected $guarded = [];

    const STATUS_SAVED = 1;
    const STATUS_TEMP = 0;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class,'patient_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assessmentFormTemplate()
    {
        return $this->belongsTo(AssessmentForm::class,'assessment_form_id');
    }

    /**
     * @param null $permission
     * @return string
     */
    public function getFileLink($permission = null)
    {

        return config('nextcloud.baseUri') . '/index.php/s/' . $this->file_link;
    }

    /**
     * @return string
     * @internal param null $permission
     */
    public function getS3Link()
    {

        return config('nextcloud.baseUri') . '/index.php/s/' . $this->file_link;
    }

    /**
     * @return string
     * @internal param null $permission
     */
    public function getS3Key()
    {

        return 'urn:oid:' . $this->s3_file_id;
    }

    public function isTemp()
    {
        return $this->status == self::STATUS_TEMP;
    }

    public function scopeInitialAssessment($query) {
        $ids = AssessmentForm::getFileTypeIDsLikeInitialAssessment();

        return $query->whereIn('assessment_form_id', $ids);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeDischarge($query) {
        $ids = AssessmentForm::getFileTypeIDsLikeDischarge();

        return $query->whereIn('assessment_form_id', $ids);
    }
}

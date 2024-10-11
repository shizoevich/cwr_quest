<?php

namespace App;

use App\Models\Patient\Comment\PatientCommentMention;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\PatientComment
 *
 * @property int $id
 * @property int $patient_id
 * @property int|null $provider_id
 * @property int|null $admin_id
 * @property string $comment
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property bool $is_system_comment
 * @property int|null $default_comment_id
 * @property-read \App\PatientDefaultComment|null $defaultComment
 * @property-read \Illuminate\Database\Eloquent\Collection|PatientCommentMention[] $mentions
 * @property-read \App\Patient $patient
 * @property-read \App\Provider|null $provider
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientComment whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientComment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientComment whereDefaultCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientComment whereIsSystemComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientComment wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientComment whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientComment whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $deleted_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\PatientComment onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientComment whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\PatientComment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\PatientComment withoutTrashed()
 */
class PatientComment extends Model {

    use SoftDeletes;

    const DEFAULT_COMMENT_TYPE = 1;

    const CANCELLATION_COMMENT_TYPE = 2;

    const RESCHEDULE_COMMENT_TYPE = 3;

    const CREATION_COMMENT_TYPE = 4;

    const INITIAL_SURVEY_COMMENT_TYPE = 5;

    const ONBOARDING_COMPLETE_TYPE = 6;

    const CHANGE_VISIT_FREQUENCY_TYPE = 7;

    const SECOND_SURVEY_COMMENT_TYPE = 8;

    const START_FILLING_REFERRAL_FORM_COMMENT_TYPE = 9;
    
    protected $table = 'patient_comments';

    protected $casts = [
        'is_system_comment' => 'boolean',
        'metadata' => 'array',
    ];

    protected $guarded = ['id'];

    public function patient() {
        return $this->belongsTo('\App\Patient');
    }

    public function provider() {
        return $this->belongsTo('\App\Provider');
    }

    public function admin() {
        return $this->belongsTo(UserMeta::class, 'admin_id','user_id');
    }

    /**
     * @return BelongsTo
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public static function getSystemMessage($key) {
        $key = 'patient_statuses.' . $key;

        return trans($key);
    }

    public static function addSystemComment($patientId, $comment, $customComment = false, $assignType = false) {
        if (!$customComment && !$assignType) {
            $comment = static::getSystemMessage($comment);
        }

        if (is_array($patientId)) {
            if (count($patientId)) {
                $data = [];
                $now = Carbon::now();
                foreach ($patientId as $id) {
                    $patientComment = self::getCommentByPatientStatus($comment, $id);

                    $data[] = [
                        'comment' => $patientComment,
                        'patient_id' => $id,
                        'is_system_comment' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                return static::insert($data);
            }

            return false;
        }

        $patientComment = self::getCommentByPatientStatus($comment, $patientId);

        return static::create([
            'comment' => $patientComment,
            'patient_id' => $patientId,
            'is_system_comment' => true,
        ]);
    }

    public static function bulkAddComments(array $data, $isSystemComment = true) {
        $now = Carbon::now();
        foreach($data as &$item) {
            $item['is_system_comment'] = $isSystemComment;
            $item['created_at'] = $now;
            $item['updated_at'] = $now;
        }

        return static::insert($data);
    }

    public static function getCommentByPatientStatus(string $key, int $patientId, bool $useDefaultConfig = false)
    {
        $visitFrequencyId = null;
        if (!$useDefaultConfig) {
            $patient = Patient::find($patientId);
            $visitFrequencyId = $patient->visit_frequency_id;
        }

        $activeToInactiveConfig = PatientStatus::getConfig('active_to_inactive', $visitFrequencyId);
        $inactiveToLostConfig = PatientStatus::getConfig('inactive_to_lost', $visitFrequencyId);
        $activeToInactiveDaysNumber = $activeToInactiveConfig['curr'];
        $inactiveToLostDaysNumber = $inactiveToLostConfig['curr'] - $activeToInactiveDaysNumber;
        $activeToInactiveDaysText = format_number_to_words($activeToInactiveDaysNumber);
        $inactiveToLostDaysText = format_number_to_words($inactiveToLostDaysNumber);

        $comment = null;
        $variableArray = [];

        switch ($key) {
            case "active_to_inactive":
                $variableArray = [
                    'active_to_inactive_days_number' => $activeToInactiveDaysNumber,
                    'active_to_inactive_days_text' => $activeToInactiveDaysText,
                    'inactive_to_lost_days_number' => $inactiveToLostDaysNumber,
                    'inactive_to_lost_days_text' => $inactiveToLostDaysText,
                ];
                break;
            case 'inactive_to_lost':
                $variableArray = [
                    'inactive_to_lost_days_number' => $inactiveToLostDaysNumber,
                    'inactive_to_lost_days_text' => $inactiveToLostDaysText,
                ];
                break;
        }

        $comment = __('patient_statuses.' . $key, $variableArray);

        return $comment;
    }

    public function mentions() {
        return $this->hasMany(PatientCommentMention::class, 'comment_id', 'id')->where('model', 'PatientComment');
    }

    public function defaultComment() {
        return $this->belongsTo(PatientDefaultComment::class, 'default_comment_id', 'id');
    }
}

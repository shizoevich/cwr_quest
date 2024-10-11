<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * App\AssessmentForm
 *
 * @property int $id
 * @property string $title
 * @property string|null $file_name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property int $parent
 * @property bool $has_signature
 * @property int $ind
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssessmentForm initialAssessment()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssessmentForm whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssessmentForm whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssessmentForm whereHasSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssessmentForm whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssessmentForm whereInd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssessmentForm whereParent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssessmentForm whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssessmentForm whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $slug
 * @property int|null $group_id
 * @property string|null $password
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssessmentForm whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssessmentForm wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssessmentForm whereSlug($value)
 */
class AssessmentForm extends Model
{
    protected $table = 'assessment_forms';

    const INITIAL_ASSESSMENT_TYPE = 1;

    const REQUEST_FOR_REAUTHORIZATION_TYPE = 2;

    const DISCHARGE_SUMMARY_TYPE = 3;

    protected $fillable = [
        'id',
        'title',
        'document_name',
        'file_name',
        'ind',
        'slug',
        'meta'
    ];

    protected $casts = [
        'ind' => 'integer',
        'parent' => 'integer',
        'has_signature' => 'boolean'
    ];

    public function internal_path ()
    {
        return resource_path('templates/assessment/' . $this->file_name);
    }

    public static function getFileTypeIDsLikeDischarge()
    {
        $ids = Cache::rememberForever('assessment_form_type_ids_like_discharge', function() {
            return static::select('id')
                ->where('type', '=', SELF::DISCHARGE_SUMMARY_TYPE)
                ->get()
                ->pluck('id')
                ->toArray();
        });

        return $ids;
    }

    public static function getFileTypeIDsLikeInitialAssessment()
    {
        $ids = Cache::rememberForever('assessment_form_type_ids_like_initial_assessment', function() {
            return static::select('id')
                ->initialAssessment()
                ->get()
                ->pluck('id')
                ->toArray();
        });

        return $ids;
    }

    public static function getFileTypeIDsLikeReauthorization()
    {
        $ids = Cache::rememberForever('assessment_form_type_ids_like_reauthorization', function() {
            return static::select('id')
                ->where('type', '=', self::REQUEST_FOR_REAUTHORIZATION_TYPE)
                ->whereNotNull('file_name')
                ->get()
                ->pluck('id')
                ->toArray();
        });

        return $ids;
    }

    public static function scopeInitialAssessment($query)
    {
        return $query->where('type', '=', self::INITIAL_ASSESSMENT_TYPE);
    }

    /**
     * @return mixed|string
     */
    public function getDocumentNameAttribute()
    {
        return $this->attributes['document_name'] ?? $this->attributes['title'];
    }
}

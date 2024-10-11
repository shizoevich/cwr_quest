<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PatientDefaultComment
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Database\Eloquent\Collection|\App\PatientComment[] $comment
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDefaultComment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDefaultComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDefaultComment whereName($value)
 * @mixin \Eloquent
 */
class PatientDefaultComment extends Model
{
    protected $table = 'patient_default_comments';
    public $timestamps = false;

    protected $guarded = [];

    public function comment() {
        return $this->hasMany(PatientComment::class, 'default_comment_id', 'id');
    }
}

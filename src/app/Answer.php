<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Answer
 *
 * @property int $id
 * @property int $question_id
 * @property string $answer
 * @property boolean $is_correct
 * @package App\Models
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Question $question
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Answer whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Answer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Answer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Answer whereIsCorrect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Answer whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Answer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Answer extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "answers";

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        "id" => "integer",
        "question_id" => "integer",
        "answer" => "string",
        "is_correct" => "boolean",
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ["question_id", "answer", "is_correct"];

    /**
     * Get the question for the answer.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Training
 *
 * @property int $id
 * @property int $user_id
 * @property string $certificate_number
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 * @property int $score
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Provider $provider
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Training whereCertificateNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Training whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Training whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Training whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Training whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Training whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Training whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Training whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Training extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'certificate_number',
        'start_date',
        'end_date',
        'score'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'certificate_number' => 'string',
        'start_date' => 'date',
        'end_date' => 'date',
        'score' => 'integer'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['end_date', 'start_date', 'created_at', 'updated_at'];

    /**
     * Get the practice that owns the training.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

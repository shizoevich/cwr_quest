<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VisitReason extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'slug',
        'title'
    ];
    
    /**
     * @return \Illuminate\Database\Eloquent\Builder|Model|null
     */
    public static function getTelehealth()
    {
        return static::query()->where('slug', '=', 'telehealth')->first();
    }
    
    /**
     * @return int|null
     */
    public static function getTelehealthId()
    {
        return optional(static::getTelehealth())->id;
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Builder|Model|null
     */
    public static function getIndividualPsychotherapy()
    {
        return static::query()->where('slug', '=', 'individual-psychotherapy')->first();
    }
    
    /**
     * @return int|null
     */
    public static function getIndividualPsychotherapyId()
    {
        return optional(static::getIndividualPsychotherapy())->id;
    }
}

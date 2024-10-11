<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * \App\Models\Language
 *
 * @property int $id
 * @property string $officeally_id
 * @property string $slug
 * @property string $title
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Language whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Language whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Language whereTitle($value)
 * @mixin \Eloquent
 */
class Language extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'officeally_id',
        'slug',
        'title',
    ];
}

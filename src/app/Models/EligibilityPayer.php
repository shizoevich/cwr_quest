<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\EligibilityPayer
 *
 * @property int $id
 * @property string $external_id
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EligibilityPayer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EligibilityPayer whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EligibilityPayer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EligibilityPayer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EligibilityPayer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EligibilityPayer extends Model
{
    protected $fillable = [
        'external_id',
        'name',
    ];
}

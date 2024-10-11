<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\SystemMessage
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $title
 * @property string $text
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property string|null $modal_class
 * @property int $only_for_admin
 * @property-read \App\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\SystemMessage onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemMessage readedUserIds()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemMessage whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemMessage whereModalClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemMessage whereOnlyForAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemMessage whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemMessage whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemMessage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemMessage whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SystemMessage withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\SystemMessage withoutTrashed()
 * @mixin \Eloquent
 */
class SystemMessage extends Model
{
    use SoftDeletes;

    protected $table = 'system_messages';

    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function scopeReadedUserIds() {
        return $this->belongsTo(User::class);
    }
}

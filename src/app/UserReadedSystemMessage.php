<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\UserReadedSystemMessage
 *
 * @property int $user_id
 * @property int $system_message_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserReadedSystemMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserReadedSystemMessage whereSystemMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserReadedSystemMessage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserReadedSystemMessage whereUserId($value)
 * @mixin \Eloquent
 */
class UserReadedSystemMessage extends Model
{
    protected $table = "users_readed_system_messages";

    protected $guarded = [];

    public $incrementing = false;

    public function user() {
        return $this->hasOne(User::class, 'user_id', 'id');
    }
}

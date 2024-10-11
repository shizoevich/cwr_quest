<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * App\Role
 *
 * @property int $id
 * @property string $role
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereRole($value)
 * @mixin \Eloquent
 */
class Role extends Model
{
    protected $table = 'roles';

    protected $guarded = [];

    public $timestamps = false;

    public function users() {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id');
    }

    public static function getRoleId($roleName) {
        $roleName = strtolower(trim($roleName));
        $roles = Cache::rememberForever('flipped_roles', function() {
            return static::all()->pluck('id', 'role')->toArray();
        });
        if(array_key_exists($roleName, $roles)) {
            return $roles[$roleName];
        }
        return null;
    }

    public function getLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->role));
    }
}

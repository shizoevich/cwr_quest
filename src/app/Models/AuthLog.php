<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * \App\Models\AuthLog
 *
 * @property int $id
 * @property string $email
 * @property string $ip
 * @property string|null $user_agent
 * @property bool $login_with_universal_password
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthLog whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthLog whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthLog whereLoginWithUniversalPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthLog whereUserAgent($value)
 * @mixin \Eloquent
 */
class AuthLog extends Model
{
    protected $fillable = [
        'user_id',
        'ip',
        'user_agent',
        'login_with_universal_password',
    ];
    
    protected $casts = [
        'user_id' => 'int',
        'login_with_universal_password' => 'bool',
    ];
}

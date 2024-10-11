<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpdateNotificationTemplate extends Model
{
    protected $fillable = [
        'name',
        'notification_title',
        'notification_content',
    ];

    protected $casts = [
        'name' => 'string',
        'notification_title' => 'string',
    ];
}

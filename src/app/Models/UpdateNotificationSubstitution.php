<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpdateNotificationSubstitution extends Model
{
    protected $fillable = [
        'key',
        'label',
    ];

    protected $casts = [
        'key' => 'string',
        'label' => 'string',
    ];
}

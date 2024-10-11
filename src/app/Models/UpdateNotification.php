<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\User;

class UpdateNotification extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'show_date',
        'is_required',
        'title',
        'content',
    ];

    protected $casts = [
        'show_date' => 'datetime',
        'is_required' => 'boolean',
        'title' => 'string',
    ];

    protected $dates = ['deleted_at'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'update_notification_user')
            ->withPivot('opened_at', 'viewed_at', 'remind_after')
            ->withTimestamps();
    }
}

<?php

namespace App\Models;

use App\UserMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderComment extends Model
{
    protected $guarded = [];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(UserMeta::class, 'admin_id', 'user_id');
    }
}

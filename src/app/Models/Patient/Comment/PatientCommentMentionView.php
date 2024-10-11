<?php

namespace App\Models\Patient\Comment;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientCommentMentionView extends Model
{
    protected $table = 'patient_comment_mention_views';

    protected $fillable = [
        'user_id',
        'mention_id',
        'read_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mention(): BelongsTo
    {
        return $this->belongsTo(PatientCommentMention::class);
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeNotRead($query)
    {
        return $query->whereNull('read_at');
    }
}

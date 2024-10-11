<?php

namespace App\Models\Patient\Comment;

use App\Models\Patient\Lead\PatientLeadComment;
use App\PatientComment;
use App\PatientDocumentComment;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientCommentMention extends Model
{
    protected $table = 'patient_comment_mentions';

    protected $fillable = [
        'user_id',
        'comment_id',
        'model',
        'created_at',
        'updated_at'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(PatientComment::class, 'comment_id', 'id');
    }

    public function documentComment(): BelongsTo
    {
        return $this->belongsTo(PatientDocumentComment::class, 'comment_id', 'id');
    }

    public function leadComment(): BelongsTo
    {
        return $this->belongsTo(PatientLeadComment::class, 'comment_id', 'id');
    }

    public function views(): HasMany
    {
        return $this->hasMany(PatientCommentMentionView::class, 'mention_id', 'id');
    }

    public function addViewForUser(int $userId, $readAt = null): void
    {
        PatientCommentMentionView::updateOrCreate([
            'mention_id' => $this->id,
            'user_id' => $userId,
        ], [
            'read_at' => $readAt ?? Carbon::now(),
        ]);
    }

    public function addViewsForAllSecretaries($readAt = null): void
    {
        $secretariesIds = User::getUsersWithAccessToAllSidebarMessages();
        $data = [];

        foreach ($secretariesIds as $secretaryId) {
            if ($secretaryId === $this->user_id) {
                continue;
            }

            $data[] = [
                'user_id' => $secretaryId,
                'mention_id' => $this->id,
                'read_at' => $readAt,
            ];
        }

        if ($data) {
            PatientCommentMentionView::insert($data);
        }
    }
}

<?php

namespace App\Observers;

use App\Models\Patient\Comment\PatientCommentMention;
use App\PatientComment;

class PatientCommentObserver
{
    public function deleting(PatientComment $comment)
    {
        PatientCommentMention::query()
            ->where('comment_id', $comment->getKey())
            ->where('model', 'PatientComment')
            ->delete();
    }
}
<?php

namespace App\Repositories\NewPatientsCRM\PatientLeadComment;

use App\Models\Patient\Lead\PatientLeadComment;

class PatientLeadCommentRepository implements PatientLeadCommentRepositoryInterface
{
    public function update(PatientLeadComment $comment, array $data): PatientLeadComment
    {
        $comment->update([
            'comment' => $data['comment'],
        ]);

        $comment->load('admin');

        return $comment;
    }

    public function delete(PatientLeadComment $comment): void
    {
        $comment->delete();
    }
}